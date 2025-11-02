class VoiceDetector {
  constructor(opts = {}) {
    this.fftSize = opts.fftSize || 512;
    this.smoothing = opts.smoothing || 0.8; // EMA smoothing for level
    this.hangoverMs = opts.hangoverMs || 500; // giữ trạng thái speaking sau khi giảm
    this.minSpeakMs = opts.minSpeakMs || 150; // yêu cầu thời gian tối thiểu để coi là nói
    this.autoCalibrateMs = opts.autoCalibrateMs || 1000;
    this.autoThresholdDb = opts.autoThresholdDb || -50; // fallback
    this._running = false;

    this._audioCtx = null;
    this._stream = null;
    this._analyser = null;
    this._data = null;
    this._level = 0; // smoothed RMS
    this._thresholdDb = opts.thresholdDb; // nếu truyền vào thì dùng luôn
    this._speaking = false;

    // internal timing
    this._lastAbove = 0;
    this._lastBelow = 0;
    this._rafId = null;
  }

  async start() {
    if (this._running) return;
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      throw new Error('getUserMedia not supported');
    }

    // request stream
    this._stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    this._audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    // resume needed on some browsers where AudioContext is suspended until user gesture
    if (this._audioCtx.state === 'suspended') {
      try { await this._audioCtx.resume(); } catch (e) { /* ignore */ }
    }

    const source = this._audioCtx.createMediaStreamSource(this._stream);
    this._analyser = this._audioCtx.createAnalyser();
    this._analyser.fftSize = this.fftSize;
    this._data = new Uint8Array(this._analyser.fftSize);

    source.connect(this._analyser);

    this._running = true;

    // nếu chưa có threshold thì tự hiệu chuẩn một khoảng ngắn
    if (this._thresholdDb === undefined) {
      await this._autoCalibrate();
    }

    this._loop();
  }

  async _autoCalibrate() {
    // đo nền trong một khoảng thời gian ngắn
    const start = Date.now();
    const samples = [];
    while (Date.now() - start < this.autoCalibrateMs) {
      this._analyser.getByteTimeDomainData(this._data);
      samples.push(this._rmsFromTimeDomain(this._data));
      // small delay to avoid locking CPU
      await new Promise(r => setTimeout(r, 50));
    }
    const avgRms = samples.reduce((a,b)=>a+b,0)/samples.length || 0.0001;
    const db = 20 * Math.log10(avgRms);
    // đặt threshold chút cao hơn noise floor
    this._thresholdDb = Math.max(db + 12, this.autoThresholdDb);
  }

  _rmsFromTimeDomain(u8arr) {
    let sum = 0;
    for (let i = 0; i < u8arr.length; i++) {
      const v = (u8arr[i] - 128) / 128; // -1 .. 1
      sum += v * v;
    }
    const mean = sum / u8arr.length;
    return Math.sqrt(mean);
  }

  _loop() {
    this._analyser.getByteTimeDomainData(this._data);
    const rms = this._rmsFromTimeDomain(this._data);
    // smooth
    this._level = (this.smoothing * this._level) + ((1 - this.smoothing) * rms);
    const db = (this._level > 0) ? 20 * Math.log10(this._level) : -100;

    const now = performance.now();
    if (db >= this._thresholdDb) {
      this._lastAbove = now;
      if (!this._speaking && now - this._lastBelow > this.minSpeakMs) {
        this._setSpeaking(true);
      }
    } else {
      this._lastBelow = now;
      // giữ trạng thái speaking trong hangoverMs để tránh flicker
      if (this._speaking && now - this._lastAbove > this.hangoverMs) {
        this._setSpeaking(false);
      }
    }

    // emit level update event
    const ev = new CustomEvent('voice:level', { detail: { rms: this._level, db, thresholdDb: this._thresholdDb } });
    document.dispatchEvent(ev);

    if (this._running) {
      this._rafId = requestAnimationFrame(this._loop.bind(this));
    }
  }

  _setSpeaking(flag) {
    if (flag === this._speaking) return;
    this._speaking = flag;
    const evName = flag ? 'voice:speechstart' : 'voice:speechend';
    document.dispatchEvent(new CustomEvent(evName, { detail: { timestamp: Date.now() } }));
  }

  stop() {
    if (!this._running) return;
    this._running = false;
    if (this._rafId) cancelAnimationFrame(this._rafId);
    if (this._stream) {
      this._stream.getTracks().forEach(t => t.stop());
      this._stream = null;
    }
    if (this._audioCtx) {
      try { this._audioCtx.close(); } catch (e) { /* ignore */ }
      this._audioCtx = null;
    }
    this._analyser = null;
    this._data = null;
    this._setSpeaking(false);
  }

  isRunning() { return this._running; }
  isSpeaking() { return this._speaking; }
  getThresholdDb() { return this._thresholdDb; }
}
// export to global for simple usage
window.VoiceDetector = VoiceDetector;

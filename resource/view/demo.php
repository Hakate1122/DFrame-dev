<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bit-Buddy Pro Mascot</title>
    <style>
        body { margin: 0; overflow: hidden; background: #e0e0e0; height: 100vh; touch-action: none; }

        #mascot-container {
            position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column; align-items: center;
            z-index: 9999; user-select: none; touch-action: none;
            will-change: transform; cursor: grab;
        }

        #mascot-container:active { cursor: grabbing; }

        .bubble {
            background: white; border: 3px solid #333; border-radius: 15px;
            padding: 8px 15px; margin-bottom: 10px;
            font-family: 'Arial', sans-serif; font-size: 14px; font-weight: bold;
            opacity: 0; transition: opacity 0.2s; pointer-events: none;
            box-shadow: 4px 4px 0 rgba(0,0,0,0.1); white-space: nowrap;
        }

        .mascot {
            width: 60px; height: 60px; background-color: #4CAF50;
            border-radius: 15px; border: 4px solid #2e7d32;
            position: relative; transition: transform 0.1s ease-out, background-color 0.3s;
        }

        /* Biểu cảm */
        .mascot.happy { background-color: #FFEB3B; border-color: #FBC02D; }
        .mascot.hurt { background-color: #f44336; border-color: #b71c1c; }

        .eyes { display: flex; justify-content: space-around; padding-top: 15px; }
        .eye { width: 10px; height: 10px; background: white; border-radius: 50%; transition: 0.1s; }
        .mouth { width: 20px; height: 5px; background: #2e7d32; margin: 12px auto; border-radius: 5px; transition: 0.2s; }

        .mascot.happy .mouth { height: 10px; border-radius: 0 0 15px 15px; background: white; }
    </style>
</head>
<body>

<div id="mascot-container">
    <div class="bubble" id="speech-bubble">Xin chào!</div>
    <div class="mascot" id="mascot-body">
        <div class="eyes"><div class="eye"></div><div class="eye"></div></div>
        <div class="mouth"></div>
    </div>
</div>

<script>
class MascotGame {
    constructor() {
        this.container = document.getElementById('mascot-container');
        this.body = document.getElementById('mascot-body');
        this.bubble = document.getElementById('speech-bubble');
        
        // Cấu hình vật lý
        this.config = {
            x: window.innerWidth - 100, y: 50,
            vx: 0, vy: 0,
            gravity: 0.8, friction: 0.9, bounce: -0.6,
            jumpForce: -15
        };

        this.state = { isDragging: false, keys: {}, offset: { x: 0, y: 0 } };
        this.audioCtx = null;

        this.init();
    }

    init() {
        // Event Listeners
        window.addEventListener('keydown', e => this.state.keys[e.key] = true);
        window.addEventListener('keyup', e => this.state.keys[e.key] = false);
        window.addEventListener('resize', () => this.handleResize());

        // Mouse & Touch
        this.container.addEventListener('mousedown', e => this.onStart(e));
        document.addEventListener('mousemove', e => this.onMove(e));
        document.addEventListener('mouseup', () => this.onEnd());

        this.container.addEventListener('touchstart', e => this.onStart(e), { passive: false });
        document.addEventListener('touchmove', e => this.onMove(e), { passive: false });
        document.addEventListener('touchend', () => this.onEnd());

        this.update();
    }

    // --- ÂM THANH ---
    initAudio() {
        if (!this.audioCtx) {
            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
    }

    playSound(freq, type = 'sine', duration = 0.1) {
        if (!this.audioCtx) return;
        const osc = this.audioCtx.createOscillator();
        const gain = this.audioCtx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(10, this.audioCtx.currentTime + duration);
        gain.gain.setValueAtTime(0.1, this.audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0, this.audioCtx.currentTime + duration);
        osc.connect(gain); gain.connect(this.audioCtx.destination);
        osc.start(); osc.stop(this.audioCtx.currentTime + duration);
    }

    // --- XỬ LÝ TƯƠNG TÁC ---
    onStart(e) {
        this.initAudio();
        this.state.isDragging = true;
        const pos = e.touches ? e.touches[0] : e;
        const rect = this.container.getBoundingClientRect();
        this.state.offset.x = pos.clientX - rect.left;
        this.state.offset.y = pos.clientY - rect.top;
        this.config.vx = this.config.vy = 0;
        this.say("Thả mình ra!");
    }

    onMove(e) {
        if (!this.state.isDragging) return;
        const pos = e.touches ? e.touches[0] : e;
        this.config.x = pos.clientX - this.state.offset.x;
        this.config.y = pos.clientY - this.state.offset.y;
    }

    onEnd() {
        if (this.state.isDragging) {
            this.state.isDragging = false;
            this.playSound(600, 'sine');
            this.body.classList.add('happy');
            setTimeout(() => this.body.classList.remove('happy'), 1000);
        }
    }

    say(text) {
        this.bubble.innerText = text;
        this.bubble.style.opacity = '1';
        clearTimeout(this.bubbleTimer);
        this.bubbleTimer = setTimeout(() => this.bubble.style.opacity = '0', 2000);
    }

    handleResize() {
        const rect = this.container.getBoundingClientRect();
        this.config.x = Math.min(this.config.x, window.innerWidth - rect.width);
        this.config.y = Math.min(this.config.y, window.innerHeight - rect.height);
    }

    // --- VÒNG LẶP VẬT LÝ ---
    update() {
        const rect = this.container.getBoundingClientRect();
        const ground = window.innerHeight - rect.height - 10;
        const wall = window.innerWidth - rect.width;

        if (!this.state.isDragging) {
            // Điều khiển phím
            if (this.state.keys['ArrowLeft']) this.config.vx -= 0.8;
            if (this.state.keys['ArrowRight']) this.config.vx += 0.8;
            if (this.state.keys['ArrowUp'] && this.config.y >= ground - 5) {
                this.config.vy = this.config.jumpForce;
                this.playSound(400, 'sine');
            }

            // Vật lý rơi & Ma sát
            this.config.vy += this.config.gravity;
            this.config.vx *= this.config.friction;
            this.config.x += this.config.vx;
            this.config.y += this.config.vy;

            // Va chạm sàn
            if (this.config.y > ground) {
                if (this.config.vy > 5) {
                    this.playSound(150, 'square');
                    this.body.style.transform = `scale(1.3, 0.7)`;
                    setTimeout(() => this.body.style.transform = `scale(1, 1)`, 100);
                }
                this.config.y = ground;
                this.config.vy *= this.config.bounce;
            }

            // Va chạm tường
            if (this.config.x < 0) { this.config.x = 0; this.config.vx *= -0.5; }
            if (this.config.x > wall) { this.config.x = wall; this.config.vx *= -0.5; }
        }

        // Render
        const tilt = this.config.vx * 1.5;
        this.container.style.transform = `translate3d(${this.config.x}px, ${this.config.y}px, 0) rotate(${tilt}deg)`;

        requestAnimationFrame(() => this.update());
    }
}

// Khởi tạo Game
const game = new MascotGame();
</script>
</body>
</html>
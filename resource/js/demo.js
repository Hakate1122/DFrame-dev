(function () {
	if (typeof window.waitMessageFromBackend !== 'undefined') {
		let socket;
		const RECONNECT_MS = 3000;

		const connect = () => {
			socket = new WebSocket('wss://' + window.location.host + '/websockets');

			socket.onmessage = (evt) => {
				if (evt.data === 'ping') socket.send('pong');
				else window.waitMessageFromBackend(evt.data);
			};

			socket.onclose = () => {
				console.log('WebSocket closed. Reconnecting in ' + RECONNECT_MS / 1000 + ' seconds...');
				setTimeout(connect, RECONNECT_MS);
			};

			socket.onerror = (err) => {
				console.error('WebSocket error:', err);
				socket.close();
			};
		};

		connect();
	}

    /**
     * Detect if the device is touch-capable 
     */
	function isTouchDevice() {
		const ua = navigator.userAgent || '';
		const hasFinePointer = matchMedia('(pointer:fine)').matches;
		const hasCoarsePointer = matchMedia('(pointer:coarse)').matches;
		if (hasFinePointer) return false;
		if (/windows|win|linux x(86|64)/i.test(ua)) return false;
		if (/android|mobile|ipad|ipod|iphone/i.test(ua)) return true;
		if (hasCoarsePointer) return true;
		return true;
	}

	/**
     * Calculate average angle between three recent mouse points
     * @param {*} event 
     * @returns 
     */
	function averageAngle(event) {
		const point = { x: event.clientX, y: event.clientY };
		averageAngle.points = averageAngle.points || [];
		averageAngle.sum = averageAngle.sum || 0;
		averageAngle.count = averageAngle.count || 0;
		averageAngle.points.push(point);
		if (averageAngle.points.length > 3) averageAngle.points.shift();
		if (averageAngle.points.length < 3) return 0;

		const [p0, p1, p2] = averageAngle.points;
		const v1 = { x: p1.x - p0.x, y: p1.y - p0.y };
		const v2 = { x: p2.x - p1.x, y: p2.y - p1.y };
		const len1 = Math.hypot(v1.x, v1.y);
		const len2 = Math.hypot(v2.x, v2.y);
		if (len1 === 0 || len2 === 0) return averageAngle.sum / (averageAngle.count || 1);

		const dot = v1.x * v2.x + v1.y * v2.y;
		const cos = Math.min(Math.max(dot / (len1 * len2), -1), 1);
		const angle = Math.acos(cos);
		averageAngle.sum += angle;
		averageAngle.count++;
		return averageAngle.sum / averageAngle.count;
	}

	/**
     * Calculate Bezier curve deviation for recent mouse points
     * @param {*} event 
     * @returns 
     */
	function bezierDeviation(event) {
		const SMOOTH_HISTORY = 30; // smoothing window
		const p = { x: event.clientX, y: event.clientY };
		bezierDeviation.points = bezierDeviation.points || [];
		const arr = bezierDeviation.points;
		arr.push(p);
		if (arr.length > 4) arr.shift();
		if (arr.length < 4) return 0;

		const [p0, p1, p2, p3] = arr;
		let totalErr = 0;
		for (let i = 0; i < 4; i++) {
			const t = i / 3;
			const bx = Math.pow(1 - t, 3) * p0.x + 3 * Math.pow(1 - t, 2) * t * p1.x + 3 * (1 - t) * Math.pow(t, 2) * p2.x + Math.pow(t, 3) * p3.x;
			const by = Math.pow(1 - t, 3) * p0.y + 3 * Math.pow(1 - t, 2) * t * p1.y + 3 * (1 - t) * Math.pow(t, 2) * p2.y + Math.pow(t, 3) * p3.y;
			const dx = arr[i].x - bx;
			const dy = arr[i].y - by;
			totalErr += Math.sqrt(dx * dx + dy * dy);
		}

		const avgErr = totalErr / 4;
		const isSmooth = avgErr < 2 ? 1 : 0;
		bezierDeviation.avg = bezierDeviation.avg || 0;
		bezierDeviation.count = bezierDeviation.count || 0;
		if (bezierDeviation.count >= SMOOTH_HISTORY) {
			bezierDeviation.avg = bezierDeviation.avg - bezierDeviation.avg / SMOOTH_HISTORY + isSmooth / SMOOTH_HISTORY;
		} else {
			bezierDeviation.count++;
			bezierDeviation.avg = bezierDeviation.avg + (isSmooth - bezierDeviation.avg) / bezierDeviation.count;
		}
		return bezierDeviation.avg;
	}

	function distance(x1, y1, x2, y2) {
		const dx = x2 - x1;
		const dy = y2 - y1;
		return Math.sqrt(dx * dx + dy * dy);
	}

	/**
     * Get or create a session ID stored in sessionStorage
     * @returns 
     */
	function getSessionId() {
		if (sessionStorage.getItem('sd')) return sessionStorage.getItem('sd');
		const id = Math.floor(Math.random() * 1000000000);
		sessionStorage.setItem('sd', id);
		return id;
	}

    /**
     * Encode text to Base64
     * @param {*} str 
     * @returns 
     */
	function encodeTextToBase64(str) {
		const encoder = new TextEncoder();
		const bytes = encoder.encode(str);
		const chars = String.fromCharCode(...bytes);
		return btoa(chars);
	}

    /**
     * Get page title or URL
     * @returns 
     */
	function pageTitleOrHref() {
		if (window !== window.top) return document.title || window.location.href;
		return document.title || window.location.href;
	}

	/**
     * Encrypt payload and send to server
     * @param {*} payload 
     */
	async function encryptAndSend(payload) {
		const encoder = new TextEncoder();
		const keyBytes = crypto.getRandomValues(new Uint8Array(16));
		const iv = crypto.getRandomValues(new Uint8Array(16));
		const key = await crypto.subtle.importKey('raw', keyBytes, 'AES-CBC', false, ['encrypt']);
		const encrypted = await crypto.subtle.encrypt({ name: 'AES-CBC', iv }, key, encoder.encode(payload));

		const toB64 = (arr) => btoa(String.fromCharCode(...arr));
		const s1 = toB64(keyBytes);
		const s2 = toB64(iv);
		const s3 = toB64(new Uint8Array(encrypted));
		const rand = Math.floor(Math.random() * 1000000000);

		await fetch('/index.php?ping&r1=' + rand, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ s1, s2, s3 }),
		});
	}

	/**
     * Compile event data and report to server
     * @param {number} mcd 
     * @param {string} dmg 
     */
	function reportEvent(mcd = 0, dmg = '') {
		const sid = parseInt(getSessionId(), 10);
		const durationSec = Math.floor((Date.now() - startTime) / 1000);
		const activeDurationSec = Math.floor(activeTime / 1000);
		const deviceType = isTouchDevice() ? 0 : 1; // 0 touch, 1 desktop
		const enf = doneFlag ? 1 : 0;

		const payload = {
			dct: encodeTextToBase64(pageTitleOrHref()),
			dur: encodeTextToBase64(window.location.href),
			mcd: mcd,
			dmg: dmg,
			sid: sid,
			dtp: deviceType,
			tev: totalEvents,
			evc: lastEventType,
			dly: durationSec,
			adly: activeDurationSec,
			nmv: moveCount,
			sdc: smallMoveCount,
			ndc: mediumMoveCount,
			mdc: largeMoveCount,
			bdc: bigMoveCount,
			avc: angleAvg,
			avr: bezierAvg,
			cth: coarseMoveCount,
			nbc: clickCount,
			erc: zeroCoordCount,
			enf: enf,
			nku: keyCount,
			etg: errorCount,
			st0: keyFastCount,
			st1: keyMidCount,
			st2: keySlowCount,
			st3: keyVerySlowCount,
			tse: touchStartCount,
			tee: touchEndCount,
			sce: scrollCount,
		};

		encryptAndSend(JSON.stringify(payload));
	}

	// --- State variables ---
	let deviceFlag = isTouchDevice() ? 0 : 1; // 0 touch, 1 desktop
	let doneFlag = true;
	let lastEventType = -1; // -1 unknown, 0 mousemove, 1 click, 2 keyup
	let totalEvents = 0;
	let startTime = Date.now();
	let lastKeyTime = Date.now();
	let lastEventTime = Date.now();
	let activeTime = 0;
	let moveCount = 0;
	let lastX = -1;
	let lastY = -1;
	let smallMoveCount = 0;
	let mediumMoveCount = 0;
	let largeMoveCount = 0;
	let bigMoveCount = 0;
	let angleAvg = 0;
	let bezierAvg = 0;
	let coarseMoveCount = 0;
	let clickCount = 0;
	let zeroCoordCount = 0;
	let keyCount = 0;
	let prevKeyInterval = 0;
	let repeatedKeyIntervals = 0;
	let errorCount = 0;
	let touchStartCount = 0;
	let touchEndCount = 0;
	let scrollCount = 0;
	let keyFastCount = 0;
	let keyMidCount = 0;
	let keySlowCount = 0;
	let keyVerySlowCount = 0;

	const EVENTS = ['click', 'mousemove', 'keyup', 'touchstart', 'touchend', 'scroll'];

	EVENTS.forEach((evtName) => {
		document.addEventListener(evtName, function (ev) {
			if (!ev.isTrusted) return;
			lastEventType = -1;
			totalEvents++;

			const sinceLastEvent = Date.now() - lastEventTime;
			if (sinceLastEvent < 3000) activeTime += sinceLastEvent;
			lastEventTime = Date.now();

			if (evtName === 'mousemove') {
				lastEventType = 0;
				moveCount++;
				if (lastX === -1) {
					lastX = ev.clientX;
					lastY = ev.clientY;
				}

				const dist = distance(ev.clientX, ev.clientY, lastX, lastY);
				if (dist < 5) smallMoveCount++;
				else if (dist < 15) mediumMoveCount++;
				else if (dist < 50) largeMoveCount++;
				else bigMoveCount++;

				lastX = ev.clientX;
				lastY = ev.clientY;
				if (lastX === 0 && lastY === 0) zeroCoordCount++;

				angleAvg = averageAngle(ev);
				bezierAvg = bezierDeviation(ev);
				if (bezierAvg < 1 - Math.pow(10, -2)) coarseMoveCount++;

				if ([20, 80, 200, 500, 1000].includes(moveCount) || !(moveCount % 2000)) reportEvent();
			}

			if (evtName === 'click') {
				lastEventType = 1;
				const clickDist = distance(ev.clientX, ev.clientY, lastX, lastY);
				if (clickDist > 20) zeroCoordCount++;
				clickCount++;
				if ([2, 4, 8, 12].includes(clickCount) || !(clickCount % 15)) reportEvent();
			}

			if (evtName === 'keyup') {
				lastEventType = 2;
				keyCount++;
				const interval = Date.now() - lastKeyTime;
				lastKeyTime = Date.now();
				if (interval < 80) keyFastCount++;
				else if (interval < 150) keyMidCount++;
				else if (interval < 300) keySlowCount++;
				else keyVerySlowCount++;

				if (Math.abs(prevKeyInterval - interval) < 20) repeatedKeyIntervals++;
				else repeatedKeyIntervals = 0;
				prevKeyInterval = interval;
				if (repeatedKeyIntervals > 3) errorCount++;
				if ((doneFlag && keyCount === 2) || !(keyCount % 10)) reportEvent();
			}

			if (evtName === 'touchstart') touchStartCount++;
			if (evtName === 'touchend') touchEndCount++;
			if (evtName === 'scroll') scrollCount++;

			if (!deviceFlag && ([5, 10, 20, 60].includes(totalEvents) || !(totalEvents % 100))) reportEvent();
		});
	});
})();
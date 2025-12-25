(function () {
    // Phần kết nối WebSocket nếu có hàm waitMessageFromBackend (có thể liên quan đến backend riêng)
    if (typeof window['waitMessageFromBackend'] !== 'undefined') {
        let ws;
        const reconnectDelay = 3000; // 0xbb8 = 3000ms

        const connectWebSocket = () => {
            ws = new WebSocket('wss://' + window.location.host + '/websockets');
            ws.onmessage = (event) => {
                if (event.data === 'ping') {
                    ws.send('pong');
                } else {
                    window.waitMessageFromBackend(event.data);
                }
            };
            ws.onclose = () => {
                console.log('WebSocket closed. Reconnecting in ' + reconnectDelay / 1000 + ' seconds...');
                setTimeout(connectWebSocket, reconnectDelay);
            };
            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                ws.close();
            };
        };
        connectWebSocket();
    }

    // Phát hiện thiết bị: trả về true nếu là mobile/touch device
    function isTouchDevice() {
        const userAgent = navigator.userAgent || '';
        const hasFinePointer = matchMedia('(pointer:fine)').matches;
        const hasCoarsePointer = matchMedia('(pointer:coarse)').matches;

        if (hasFinePointer) return false;                    // Chuột chính xác → desktop
        if (/windows|win|linux x(86|64)/i.test(userAgent)) return false;
        if (/android|mobile|ipad|ipod|iphone/i.test(userAgent)) return true;
        if (hasCoarsePointer) return true;
        return true;
    }

    // Tính góc quay trung bình của chuột (đo độ "xoay" khi di chuyển)
    function calculateAverageAngle(event) {
        const point = { x: event.clientX, y: event.clientY };
        calculateAverageAngle.lastPoints = calculateAverageAngle.lastPoints || [];
        calculateAverageAngle.totalAngle = calculateAverageAngle.totalAngle || 0;
        calculateAverageAngle.count = calculateAverageAngle.count || 0;

        calculateAverageAngle.lastPoints.push(point);
        if (calculateAverageAngle.lastPoints.length > 3) {
            calculateAverageAngle.lastPoints.shift();
        }
        if (calculateAverageAngle.lastPoints.length < 3) return 0;

        const [p1, p2, p3] = calculateAverageAngle.lastPoints;
        const vec1 = { x: p2.x - p1.x, y: p2.y - p1.y };
        const vec2 = { x: p3.x - p2.x, y: p3.y - p2.y };

        const len1 = Math.hypot(vec1.x, vec1.y);
        const len2 = Math.hypot(vec2.x, vec2.y);
        if (len1 === 0 || len2 === 0) return calculateAverageAngle.totalAngle / (calculateAverageAngle.count || 1);

        const dot = vec1.x * vec2.x + vec1.y * vec2.y;
        const angle = Math.acos(Math.min(Math.max(dot / (len1 * len2), -1), 1));

        calculateAverageAngle.totalAngle += angle;
        calculateAverageAngle.count++;
        return calculateAverageAngle.totalAngle / calculateAverageAngle.count;
    }

    // Đo độ mượt của đường di chuột bằng Bézier curve approximation
    function calculateCurveSmoothness(event) {
        const maxSamples = 30; // 0x1e = 30
        const point = { x: event.clientX, y: event.clientY };
        calculateCurveSmoothness.lastPoints = calculateCurveSmoothness.lastPoints || [];

        calculateCurveSmoothness.lastPoints.push(point);
        if (calculateCurveSmoothness.lastPoints.length > 4) {
            calculateCurveSmoothness.lastPoints.shift();
        }
        if (calculateCurveSmoothness.lastPoints.length < 4) return 0;

        const [p0, p1, p2, p3] = calculateCurveSmoothness.lastPoints;
        let totalDeviation = 0;

        for (let i = 0; i < 4; i++) {
            const t = i / 3;
            const bezierX = Math.pow(1 - t, 3) * p0.x + 3 * Math.pow(1 - t, 2) * t * p1.x + 3 * (1 - t) * Math.pow(t, 2) * p2.x + Math.pow(t, 3) * p3.x;
            const bezierY = Math.pow(1 - t, 3) * p0.y + 3 * Math.pow(1 - t, 2) * t * p1.y + 3 * (1 - t) * Math.pow(t, 2) * p2.y + Math.pow(t, 3) * p3.y;

            const dx = calculateCurveSmoothness.lastPoints[i].x - bezierX;
            const dy = calculateCurveSmoothness.lastPoints[i].y - bezierY;
            totalDeviation += Math.sqrt(dx * dx + dy * dy);
        }

        const avgDeviation = totalDeviation / 4;
        const isSmooth = avgDeviation < 2 ? 1 : 0;

        calculateCurveSmoothness.avg = calculateCurveSmoothness.avg || 0;
        calculateCurveSmoothness.count = calculateCurveSmoothness.count || 0;

        if (calculateCurveSmoothness.count >= maxSamples) {
            calculateCurveSmoothness.avg = calculateCurveSmoothness.avg - calculateCurveSmoothness.avg / maxSamples + isSmooth / maxSamples;
        } else {
            calculateCurveSmoothness.count++;
            calculateCurveSmoothness.avg += (isSmooth - calculateCurveSmoothness.avg) / calculateCurveSmoothness.count;
        }
        return calculateCurveSmoothness.avg;
    }

    // Tính khoảng cách Euclidean giữa 2 điểm
    function distance(x1, y1, x2, y2) {
        const dx = x2 - x1;
        const dy = y2 - y1;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Lấy hoặc tạo session ID ngẫu nhiên
    function getSessionId() {
        if (sessionStorage.getItem('sd')) {
            return sessionStorage.getItem('sd');
        } else {
            const id = Math.floor(Math.random() * 0x3b9aca00); // ~1 tỷ
            sessionStorage.setItem('sd', id);
            return id;
        }
    }

    // Base64 encode string (qua Uint8Array)
    function base64Encode(str) {
        const encoder = new TextEncoder();
        const bytes = encoder.encode(str);
        const binary = String.fromCharCode(...bytes);
        return btoa(binary);
    }

    // Lấy title (nếu trong iframe thì vẫn lấy của trang hiện tại)
    function getPageTitle() {
        const title = document.title || window.location.href;
        return title;
    }

    // Gửi dữ liệu thu thập được qua POST (mã hóa AES-CBC)
    async function sendData(payloadJson) {
        const keyRaw = crypto.getRandomValues(new Uint8Array(16));
        const iv = crypto.getRandomValues(new Uint8Array(16));

        const key = await crypto.subtle.importKey('raw', keyRaw, 'AES-CBC', false, ['encrypt']);
        const encrypted = await crypto.subtle.encrypt({ name: 'AES-CBC', iv: iv }, key, new TextEncoder().encode(payloadJson));

        const toBase64 = arr => btoa(String.fromCharCode(...arr));

        const s1 = toBase64(keyRaw);
        const s2 = toBase64(iv);
        const s3 = toBase64(new Uint8Array(encrypted));

        const randomParam = Math.floor(Math.random() * 0x3b9aca00);

        await fetch('/index.php?ping&r1=' + randomParam, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ s1, s2, s3 })
        });
    }

    // Thu thập và gửi dữ liệu telemetry
    function collectAndSend(mouseCurveDeviation = 0, debugMsg = '') {
        const sessionId = parseInt(getSessionId());
        const sessionDurationSec = Math.floor((Date.now() - sessionStartTime) / 1000);
        const totalEventDelaySec = Math.floor(totalInterEventDelay / 1000);
        const isDesktop = isTouchDevice() ? 0 : 1;
        const isFirstSend = firstKeyPress ? 1 : 0;

        const payload = {
            dct: base64Encode(getPageTitle()),               // document title
            dur: base64Encode(window.location.href),         // document url
            mcd: mouseCurveDeviation,                        // mouse curve deviation
            dmg: debugMsg,
            sid: sessionId,
            dtp: isDesktop,                                  // device type (0=mobile,1=desktop)
            tev: totalEvents,                                // total events
            evc: lastEventTypeCode,                          // last event code
            dly: sessionDurationSec,
            adly: totalEventDelaySec,
            nmv: mouseMoves,
            sdc: smallMovesCount,    // <5px
            ndc: normalMovesCount,   // <15px
            mdc: mediumMovesCount,   // <50px
            bdc: bigMovesCount,      // >=50px
            avc: averageAngle,
            avr: averageSmoothness,
            cth: curveTooHighCount,
            nbc: clickCount,
            erc: edgeClickCount,     // click tại (0,0) hoặc xa
            enf: isFirstSend,
            nku: keyUpCount,
            etg: repeatedTimingCount,
            st0: keyDelayUnder80Count,   // <80ms
            st1: keyDelay80_150Count,
            st2: keyDelay150_300Count,
            st3: keyDelay300_1000Count,
            tse: touchStartCount,
            tee: touchEndCount,
            sce: scrollCount
        };

        sendData(JSON.stringify(payload));
    }

    // Khởi tạo các biến theo dõi
    const isMobile = isTouchDevice() ? 0 : 1;
    let firstKeyPress = true;

    let lastEventTypeCode = -1;
    let totalEvents = 0;
    const sessionStartTime = Date.now();
    let lastKeyTime = Date.now();
    let lastAnyEventTime = Date.now();
    let totalInterEventDelay = 0;

    let mouseMoves = 0;
    let prevMouseX = -1;
    let prevMouseY = -1;

    let smallMovesCount = 0;   // <5px
    let normalMovesCount = 0; // <15px
    let mediumMovesCount = 0; // <50px
    let bigMovesCount = 0;    // >=50px

    let averageAngle = 0;
    let averageSmoothness = 0;
    let curveTooHighCount = 0;

    let clickCount = 0;
    let edgeClickCount = 0;

    let keyUpCount = 0;
    let lastKeyInterval = 0;
    let consecutiveSimilarInterval = 0;
    let repeatedTimingCount = 0;

    let keyDelayUnder80Count = 0;
    let keyDelay80_150Count = 0;
    let keyDelay150_300Count = 0;
    let keyDelay300_1000Count = 0;

    let touchStartCount = 0;
    let touchEndCount = 0;
    let scrollCount = 0;

    // Đăng ký listener cho các sự kiện
    ['click', 'mousemove', 'keyup', 'touchstart', 'touchend', 'scroll'].forEach(eventName => {
        document.addEventListener(eventName, function (event) {
            if (event.isTrusted) {  // Chỉ xử lý sự kiện thật từ người dùng
                lastEventTypeCode = -1;
                totalEvents++;

                const timeSinceLastEvent = Date.now() - lastAnyEventTime;
                if (timeSinceLastEvent < 3000) {  // 0xbb8 = 3000ms
                    totalInterEventDelay += timeSinceLastEvent;
                }
                lastAnyEventTime = Date.now();

                if (eventName === 'mousemove') {
                    lastEventTypeCode = 0;
                    mouseMoves++;

                    if (prevMouseX === -1) {
                        prevMouseX = event.clientX;
                        prevMouseY = event.clientY;
                    }

                    const dist = distance(event.clientX, event.clientY, prevMouseX, prevMouseY);

                    if (dist < 5) smallMovesCount++;
                    else if (dist < 15) normalMovesCount++;
                    else if (dist < 50) mediumMovesCount++;
                    else bigMovesCount++;

                    prevMouseX = event.clientX;
                    prevMouseY = event.clientY;

                    if (event.clientX === 0 && event.clientY === 0) edgeClickCount++;

                    averageAngle = calculateAverageAngle(event);
                    averageSmoothness = calculateCurveSmoothness(event);

                    if (averageSmoothness < 0.1 - Math.pow(10, -2)) curveTooHighCount++;

                    // Gửi dữ liệu định kỳ theo số lần mousemove
                    if ([20, 80, 200, 500, 1000].includes(mouseMoves) || !(mouseMoves % 2000)) {
                        collectAndSend();
                    }
                }

                if (eventName === 'click') {
                    lastEventTypeCode = 1;
                    const dist = distance(event.clientX, event.clientY, prevMouseX, prevMouseY);
                    if (dist > 20) edgeClickCount++;
                    clickCount++;

                    if ([2, 4, 8, 12].includes(clickCount) || !(clickCount % 15)) {
                        collectAndSend();
                    }
                }

                if (eventName === 'keyup') {
                    lastEventTypeCode = 2;
                    keyUpCount++;
                    const interval = Date.now() - lastKeyTime;
                    lastKeyTime = Date.now();

                    if (interval < 80) keyDelayUnder80Count++;
                    else if (interval < 150) keyDelay80_150Count++;
                    else if (interval < 300) keyDelay150_300Count++;
                    else if (interval < 1000) keyDelay300_1000Count++;

                    if (Math.abs(lastKeyInterval - interval) < 20) {
                        consecutiveSimilarInterval++;
                    } else {
                        consecutiveSimilarInterval = 0;
                    }
                    lastKeyInterval = interval;

                    if (consecutiveSimilarInterval > 3) repeatedTimingCount++;

                    if ((firstKeyPress && keyUpCount === 2) || !(keyUpCount % 10)) {
                        collectAndSend();
                    }
                }

                if (eventName === 'touchstart') touchStartCount++;
                if (eventName === 'touchend') touchEndCount++;
                if (eventName === 'scroll') scrollCount++;

                // Gửi định kỳ theo tổng số sự kiện (chỉ trên desktop)
                if (!isMobile && ([5, 10, 20, 60].includes(totalEvents) || !(totalEvents % 100))) {
                    collectAndSend();
                }
            }
        });
    });
})();
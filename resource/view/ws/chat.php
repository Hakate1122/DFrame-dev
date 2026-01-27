
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WebSocket Chat</title>
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body { font-family: Arial, sans-serif; background:#fff; }

        .chat-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        h1 { margin: 0 0 12px 0; font-size: 1.6rem; }

        #messages {
            border: 1px solid #ccc;
            background: #fff;
            padding: 10px;
            margin-bottom: 10px;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 180px;
            font-size: 14px;
        }

        #inputArea { display: flex; gap: 8px; align-items: center; }

        #messageInput {
            flex: 1 1 auto;
            padding: 10px;
            font-size: 14px;
            min-width: 0;
        }

        button {
            padding: 10px 14px;
            font-size: 14px;
            cursor: pointer;
            flex: 0 0 auto;
        }

        @media (min-width: 600px) {
            .chat-container { padding: 20px; }
            #messages { font-size: 15px; }
        }

        @media (max-width: 420px) {
            h1 { font-size: 1.4rem; }
            #messageInput { padding: 8px; font-size: 13px; }
            button { padding: 8px 10px; font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>WebSocket Chat</h1>
        <div id="messages" aria-live="polite"></div>

        <div id="inputArea">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button id="sendBtn">Send</button>
        </div>
    </div>

    <script>
        var inputEl = document.getElementById('messageInput');
        var sendBtn = document.getElementById('sendBtn');

        var logoutBtn = document.createElement('button');
        logoutBtn.id = 'logoutBtn';
        logoutBtn.textContent = 'Logout';
        logoutBtn.style.flex = '0 0 auto';
        logoutBtn.style.marginLeft = '6px';
        document.getElementById('inputArea').appendChild(logoutBtn);

        inputEl.disabled = true;
        sendBtn.disabled = true;
        logoutBtn.disabled = true;

        var host = window.location.hostname;
        var port = 9501;
        var scheme = window.location.protocol === 'https:' ? 'wss' : 'ws';
        var wsUrl = scheme + '://' + host + ':' + port;

        var ws;
        try {
            ws = new WebSocket(wsUrl);
        } catch (e) {
            addMessage('Error: could not create WebSocket - ' + (e && e.message ? e.message : e));
            throw e;
        }

        ws.onopen = function() {
            addMessage('Connected to chat server (' + wsUrl + ')');
            inputEl.disabled = false;
            sendBtn.disabled = false;
            logoutBtn.disabled = false;
            inputEl.focus();
        };

        ws.onmessage = function(event) {
            addMessage(event.data);
        };

        ws.onclose = function(event) {
            var msg = 'Disconnected from server';
            if (event && event.code) msg += ' (code ' + event.code + ')';
            addMessage(msg);
            inputEl.disabled = true;
            sendBtn.disabled = true;
            logoutBtn.disabled = true;
            try { console.info('WebSocket closed', event); } catch (e) {}
        };

        ws.onerror = function(evt) {
            var errText = '[object Event]';
            if (evt && evt.message) errText = evt.message;
            else if (evt && evt.type) errText = evt.type;
            addMessage('Error: ' + errText);
            try { console.error('WebSocket error', evt); } catch (e) {}
        };

        function sendMessage() {
            if (!ws || ws.readyState !== WebSocket.OPEN) {
                addMessage('Not connected to server.');
                return;
            }
            var message = inputEl.value.replace(/^\s+|\s+$/g, '');
            if (message) {
                try { ws.send(message); } catch (e) { addMessage('Send error: ' + (e && e.message ? e.message : e)); }
                inputEl.value = '';
            }
        }

        if (sendBtn.addEventListener) {
            sendBtn.addEventListener('click', sendMessage);
        } else if (sendBtn.attachEvent) {
            sendBtn.attachEvent('onclick', sendMessage);
        }

        if (inputEl.addEventListener) {
            inputEl.addEventListener('keypress', function(e) {
                var key = e.which || e.keyCode;
                if (key === 13) { sendMessage(); }
            });
        } else if (inputEl.attachEvent) {
            inputEl.attachEvent('onkeypress', function(e) {
                var key = e.which || e.keyCode;
                if (key === 13) { sendMessage(); }
            });
        }

        function logout() {
            if (!ws) {
                addMessage('WebSocket not initialized.');
                return;
            }
            if (ws.readyState === WebSocket.OPEN) {
                try {
                    ws.close(1000, 'Client logout');
                    addMessage('You have logged out.');
                } catch (e) {
                    addMessage('Logout error: ' + (e && e.message ? e.message : e));
                }
            } else {
                addMessage('Already disconnected.');
            }
            inputEl.disabled = true;
            sendBtn.disabled = true;
            logoutBtn.disabled = true;
        }

        if (logoutBtn.addEventListener) {
            logoutBtn.addEventListener('click', logout);
        } else if (logoutBtn.attachEvent) {
            logoutBtn.attachEvent('onclick', logout);
        }

        function addMessage(message) {
            var messages = document.getElementById('messages');
            var div = document.createElement('div');
            try {
                div.textContent = (new Date()).toLocaleTimeString() + ': ' + message;
            } catch (e) {
                div.innerText = (new Date()).toLocaleTimeString() + ': ' + message;
            }
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }
    </script>
</body>
</html>
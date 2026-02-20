<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>P2P File Transfer</title>
</head>
<body>

<h2>P2P File Transfer</h2>

Room: <input id="room" value="room1">
<button onclick="connect()">Connect</button>
<hr>

<input type="file" id="fileInput">
<button onclick="sendFile()">Send File</button>

<pre id="log"></pre>

<script>
var pc, dc, ws;
var chunkSize = 16000;
var fileReader;
var receivedBuffers = [];
var receivedSize = 0;
var fileMeta = null;

function log(t){ document.getElementById('log').textContent += t + "\n"; }

function connect(){
    ws = new WebSocket("ws://192.168.137.1:3000");
    ws.onmessage = onSignal;

    pc = new RTCPeerConnection();

    pc.ondatachannel = function(e){
        dc = e.channel;
        setupDataChannel();
    };

    ws.onopen = async function(){
        dc = pc.createDataChannel("file");
        setupDataChannel();

        var offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        sendSignal({sdp: offer});
    };

    pc.onicecandidate = function(e){
        if(e.candidate) sendSignal({candidate: e.candidate});
    };
}

function sendSignal(data){
    data.room = document.getElementById('room').value;
    ws.send(JSON.stringify(data));
}

async function onSignal(msg){
    var data = JSON.parse(msg.data);

    if(data.sdp){
        await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
        if(data.sdp.type === 'offer'){
            var answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            sendSignal({sdp: answer});
        }
    }

    if(data.candidate){
        await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
    }
}

function setupDataChannel(){
    dc.binaryType = 'arraybuffer';

    dc.onopen = function(){ log("DataChannel Open"); };

    dc.onmessage = function(e){
        if(typeof e.data === "string"){
            fileMeta = JSON.parse(e.data);
            receivedBuffers = [];
            receivedSize = 0;
            log("Receiving: " + fileMeta.name);
        }else{
            receivedBuffers.push(e.data);
            receivedSize += e.data.byteLength;

            if(receivedSize === fileMeta.size){
                var blob = new Blob(receivedBuffers);
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = fileMeta.name;
                a.click();
                log("File received!");
            }
        }
    };
}

function sendFile(){
    var file = document.getElementById('fileInput').files[0];
    if(!file) return;

    dc.send(JSON.stringify({name: file.name, size: file.size}));

    fileReader = new FileReader();
    var offset = 0;

    fileReader.onload = function(e){
        dc.send(e.target.result);
        offset += e.target.result.byteLength;

        if(offset < file.size){
            readSlice(offset);
        }else{
            log("File sent!");
        }
    };

    function readSlice(o){
        var slice = file.slice(o, o + chunkSize);
        fileReader.readAsArrayBuffer(slice);
    }

    readSlice(0);
}
</script>
</body>
</html>

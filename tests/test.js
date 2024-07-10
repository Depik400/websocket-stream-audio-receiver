const fs = require('fs');
const path = require('path');

const websocket = new WebSocket('ws://localhost:8000/file-saver');

websocket.onmessage = (msg) => {
    console.log(msg);
}

websocket.onopen = () => {
    websocket.send(JSON.stringify({event: 'init'}))

    const stream = fs.createReadStream(path.resolve(__dirname, 'source.mp3'));
    
    
    stream.on('data', (data) => {
        websocket.send(data);
    })

    stream.on('close', () => {
        websocket.send(JSON.stringify({event: 'close'}))
    })
}





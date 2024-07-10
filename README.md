### Установка проекта

Обязательно нужно выполнить последний вызов, это нужно для кеша

```bash
cp .env.example .env
composer install
composer dump-autoload --optimize
```

###

Вебсокеты висят на маршруте **_/file-saver_**

### Описание

Для того, чтобы создать поток, необходимо инициализовать файл

1. Для этого необходимо отправить в сокет json вида

```json
{
  "event": "init"
}
```

2. Сокет готов для записи, можно передавать бинарные данные, в случае с js это либо blob, либо arraybuffer, либо Buffer
3. По окончании необходимо вызывать

```json
{
  "event": "close"
}
```

### Пример работы

**Только для node.js 22**

В папке tests лежит файл test.js <br/>
Он отправит по сокетам source.mp3 на сервер

Необходимо запустить php-server

```bash
php main.php start
```

После этого

```bash
node tests/test.js
```

> Если меняли .env, нужно в файле tests/test.js строку подключения

### Пример для фронтенда

```js
const socket = new WebSocket('ws://localhost:8080/file-saver');
const isRecording = false;
function recording() {
  if (isRecording) {
    stopMedia();
  } else {
    startMedia();
  }
}
let mediaRecorder = null;
function stopMedia() {
  isRecording = false;
  mediaRecorder?.stop();
}

async function startMedia() {
  isRecording = true;
  socket.send(JSON.stringify({ event: 'init', body: '' }));
  const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
  mediaRecorder = new MediaRecorder(stream, {
    audioBitsPerSecond: 128000,
    mimeType: 'audio/webm;codecs=opus',
  });
  mediaRecorder.ondataavailable = async (body) => {
    console.log('data available');
    socket.send(await body.data.arrayBuffer());
  };

  mediaRecorder.onstop = async (body) => {
    console.log(body);
    socket.send(JSON.stringify({ event: 'close', body: '' }));
  };

  mediaRecorder.start(1000);
  console.log(mediaRecorder.state);
}
```

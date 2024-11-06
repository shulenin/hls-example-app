<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<video id="video" controls></video>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
    if (Hls.isSupported()) {
        const video = document.getElementById('video');
        const hls = new Hls();

        hls.autoLevelEnabled = true;

        hls.loadSource('/storage/Mf35BP2R30IJIPQbRAlm5vo31frepC52Qy9AIfnL.m3u8');
        hls.attachMedia(video);

        hls.on(Hls.Events.LEVEL_SWITCHED, function(event, data) {
            console.log('Switched to quality level:', data.level);
        });

        hls.on(Hls.Events.MANIFEST_PARSED, function () {
            video.play();
        });
    }
</script>
</body>
</html>
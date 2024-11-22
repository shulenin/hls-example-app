<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Upload & Player</title>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
      body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
      }

      .upload-container {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        margin-bottom: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .upload-container:hover {
        border-color: #2196F3;
        background-color: #f8f9fa;
      }

      .upload-container.dragover {
        border-color: #2196F3;
        background-color: #e3f2fd;
      }

      .btn {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        margin: 10px;
        transition: all 0.3s ease;
      }

      .btn-primary {
        background-color: #2196F3;
        color: white;
      }

      .btn-primary:hover {
        background-color: #1976D2;
      }

      .btn-primary:disabled {
        background-color: #BDBDBD;
        cursor: not-allowed;
      }

      .player-container {
        display: none;
        margin-top: 20px;
      }

      video {
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        display: block;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      }

      .quality-selector {
        margin: 10px 0;
        text-align: center;
      }

      .quality-info {
        background-color: rgba(0, 0, 0, 0.05);
        padding: 8px 12px;
        border-radius: 4px;
        margin: 10px 0;
        font-size: 14px;
        color: #333;
        text-align: center;
      }

      .quality-selector {
        margin: 15px 0;
        text-align: center;
      }

      .quality-selector select {
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background-color: white;
        font-size: 14px;
        min-width: 150px;
      }

      .quality-selector select:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
      }

      .status {
        text-align: center;
        margin: 10px 0;
        padding: 10px;
        border-radius: 4px;
      }

      .status.error { background-color: #ffebee; color: #c62828; }
      .status.success { background-color: #e8f5e9; color: #2e7d32; }
      .status.info { background-color: #e3f2fd; color: #1565c0; }

      .progress-container {
        margin: 20px 0;
        display: none;
      }

      .progress-bar {
        height: 20px;
        background-color: #f0f0f0;
        border-radius: 10px;
        overflow: hidden;
      }

      .progress-fill {
        height: 100%;
        background-color: #4CAF50;
        width: 0;
        transition: width 0.3s ease;
      }

      .controls-container {
        text-align: center;
        margin: 20px 0;
        display: none;
      }

      .conversion-status {
        background-color: #e3f2fd;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        text-align: center;
        display: none;
      }

      .spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
        vertical-align: middle;
      }

      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }

      .status-icon {
        display: inline-block;
        width: 20px;
        height: 20px;
        margin-right: 10px;
        vertical-align: middle;
      }

      .status-icon.loading:after {
        content: "⏳";
      }

      .status-icon.success:after {
        content: "✅";
      }

      .status-icon.error:after {
        content: "❌";
      }
    </style>
</head>
<body>
<div class="upload-container" id="dropZone">
    <h2>Загрузить видео</h2>
    <p>Перетащите файл сюда или кликните для выбора</p>
    <input type="file" id="fileInput" accept="video/*" style="display: none">
</div>

<div class="status" id="statusMessage"></div>

<div class="progress-container" id="progressContainer">
    <h3>Загрузка файла</h3>
    <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
    </div>
    <p id="progressText">0%</p>
</div>

<div class="controls-container" id="controlsContainer">
    <button id="loadVideoBtn" class="btn btn-primary">Загрузить видео</button>
</div>

<div class="player-container" id="playerContainer">
    <video id="video" controls></video>
    <div class="quality-selector">
        <label for="qualitySelect">Качество:</label>
        <select id="qualitySelect"></select>
    </div>
    <div id="qualityInfo" class="status info"></div>
</div>

<div class="conversion-status" id="conversionStatus">
    <div class="spinner"></div>
    <span id="statusText">Проверка статуса конвертации...</span>
</div>

<script>
    class VideoProcessor {
        constructor() {
            // Основные элементы интерфейса
            this.dropZone = document.getElementById('dropZone');
            this.fileInput = document.getElementById('fileInput');
            this.progressContainer = document.getElementById('progressContainer');
            this.progressFill = document.getElementById('progressFill');
            this.progressText = document.getElementById('progressText');
            this.playerContainer = document.getElementById('playerContainer');
            this.statusMessage = document.getElementById('statusMessage');
            this.video = document.getElementById('video');
            this.qualitySelect = document.getElementById('qualitySelect');
            this.qualityInfo = document.getElementById('qualityInfo');
            this.loadVideoBtn = document.getElementById('loadVideoBtn');
            this.controlsContainer = document.getElementById('controlsContainer');
            this.conversionStatus = document.getElementById('conversionStatus');
            this.statusText = document.getElementById('statusText');

            // Состояние
            this.currentVideoId = null;
            this.statusCheckInterval = null;
            this.processing = false;

            this.initializeEventListeners();
        }

        initializeEventListeners() {
            // Обработка Drag & Drop
            this.dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.dropZone.classList.add('dragover');
            });

            this.dropZone.addEventListener('dragleave', () => {
                this.dropZone.classList.remove('dragover');
            });

            this.dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                this.dropZone.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file) this.handleFileUpload(file);
            });

            // Обработка выбора файла
            this.dropZone.addEventListener('click', () => {
                this.fileInput.click();
            });

            this.fileInput.addEventListener('change', () => {
                const file = this.fileInput.files[0];
                if (file) this.handleFileUpload(file);
            });
        }

        async handleFileUpload(file) {
            if (!file.type.startsWith('video/')) {
                this.showStatus('Пожалуйста, выберите видео файл', 'error');
                return;
            }

            this.showProgress();
            this.showStatus('Загрузка файла...', 'info');

            const formData = new FormData();
            formData.append('content', file); // Изменено с 'video' на 'content'

            try {
                const response = await fetch('/api/content/create', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Ошибка загрузки файла');
                }

                const data = await response.json();
                this.currentFileName = data.fileName;

                this.showStatus('Файл загружен. Начинается конвертация...', 'info');
                this.startStatusChecking();

            } catch (error) {
                this.showStatus(`Ошибка при загрузке файла: ${error.message}`, 'error');
                this.hideProgress();
            }
        }

        startStatusChecking() {
            this.processing = true;
            this.conversionStatus.style.display = 'block';
            this.updateConversionStatus('Идет конвертация видео...', 'loading');

            // Проверяем статус каждые 3 секунды
            this.statusCheckInterval = setInterval(() => this.checkStatus(), 6000);
        }

        async checkStatus() {
            if (!this.currentFileName || !this.processing) {
                this.stopStatusChecking();
                return;
            }

            try {
                const response = await fetch(`/api/content/status/${this.currentFileName}`);
                const data = await response.json();

                // Обновляем прогресс в любом случае, если он есть
                if (typeof data.progress === 'number') {
                    this.updateProgress(data.progress);
                }

                switch (data.status) {
                    case 'completed':
                        this.processing = false;
                        this.stopStatusChecking();
                        this.updateConversionStatus('Конвертация завершена!', 'success');
                        this.showStatus('Видео готово к просмотру', 'success');
                        this.hideProgress();
                        this.initializePlayer();
                        break;
                    // ... остальные case остаются без изменений
                }
            } catch (error) {
                this.showStatus('Ошибка при проверке статуса конвертации', 'error');
                this.stopStatusChecking();
            }
        }

        stopStatusChecking() {
            if (this.statusCheckInterval) {
                clearInterval(this.statusCheckInterval);
                this.statusCheckInterval = null;
            }
        }

        async initializePlayer() {
            try {
                if (!this.currentFileName) {
                    throw new Error('Имя файла не определено');
                }

                const response = await fetch(`/api/content/read/${this.currentFileName}`);

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Не удалось получить данные видео');
                }

                const data = await response.json();
                if (!data.content) {
                    throw new Error('URL видео не получен');
                }

                // Используем URL как есть, без модификаций
                const videoUrl = data.content;
                console.log('Loading video from URL:', videoUrl); // Для отладки

                if (Hls.isSupported()) {
                    const hls = new Hls({
                        debug: true, // Включаем отладку
                        enableWorker: true
                    });

                    hls.loadSource(videoUrl);
                    hls.attachMedia(this.video);

                    hls.on(Hls.Events.MANIFEST_PARSED, () => {
                        console.log('HLS manifest parsed successfully');
                        this.setupQualitySelector(hls);
                        this.playerContainer.style.display = 'block';
                        this.video.play().catch(e => {
                            console.error('Autoplay failed:', e);
                        });
                    });

                    hls.on(Hls.Events.ERROR, (event, data) => {
                        console.error('HLS Error:', data);
                        if (data.fatal) {
                            switch(data.type) {
                                case Hls.ErrorTypes.NETWORK_ERROR:
                                    console.log('Network error - attempting recovery');
                                    hls.startLoad();
                                    break;
                                case Hls.ErrorTypes.MEDIA_ERROR:
                                    console.log('Media error - attempting recovery');
                                    hls.recoverMediaError();
                                    break;
                                default:
                                    console.error('Fatal error:', data);
                                    this.showStatus('Ошибка воспроизведения видео', 'error');
                                    break;
                            }
                        }
                    });
                } else if (this.video.canPlayType('application/vnd.apple.mpegurl')) {
                    this.video.src = videoUrl;
                    this.playerContainer.style.display = 'block';
                } else {
                    this.showStatus('Ваш браузер не поддерживает воспроизведение HLS', 'error');
                }
            } catch (error) {
                console.error('Player initialization error:', error);
                this.showStatus(`Ошибка инициализации плеера: ${error.message}`, 'error');
            }
        }

        setupQualitySelector(hls) {
            this.qualitySelect.innerHTML = '<option value="-1">Авто</option>';

            // Сортируем уровни по высоте (разрешению)
            const sortedLevels = [...hls.levels].sort((a, b) => b.height - a.height);

            sortedLevels.forEach((level, index) => {
                const option = document.createElement('option');
                option.value = index;
                const bitrate = Math.round(level.bitrate / 1000);
                // Проверяем, что высота определена корректно
                const height = level.height || level.width / (16/9); // предполагаем соотношение сторон 16:9 если высота не определена
                option.text = `${height}p (${bitrate} Kbps)`;
                this.qualitySelect.appendChild(option);
            });

            this.qualitySelect.addEventListener('change', (e) => {
                const selectedLevel = parseInt(e.target.value);
                hls.currentLevel = selectedLevel;
                this.updateQualityInfo(hls);
            });

            hls.on(Hls.Events.LEVEL_SWITCHED, () => {
                this.updateQualityInfo(hls);
            });

            this.updateQualityInfo(hls);
        }

        updateQualityInfo(hls) {
            const currentLevel = hls.levels[hls.currentLevel];
            const isAuto = hls.autoLevelEnabled;

            if (currentLevel) {
                const bitrate = Math.round(currentLevel.bitrate / 1000);
                const height = currentLevel.height || currentLevel.width / (16/9);
                const qualityText = isAuto ?
                    `Авто (текущее: ${height}p @ ${bitrate} Kbps)` :
                    `${height}p @ ${bitrate} Kbps`;

                this.qualityInfo.textContent = `Текущее качество: ${qualityText}`;
            }
        }

        updateConversionStatus(message, type = 'loading') {
            this.statusText.textContent = message;
            const spinner = this.conversionStatus.querySelector('.spinner');
            spinner.style.display = type === 'loading' ? 'inline-block' : 'none';
        }

        showStatus(message, type) {
            this.statusMessage.textContent = message;
            this.statusMessage.className = `status ${type}`;
            this.statusMessage.style.display = 'block';
        }

        showProgress() {
            this.progressContainer.style.display = 'block';
        }

        hideProgress() {
            this.progressContainer.style.display = 'none';
        }

        updateProgress(percent) {
            this.progressFill.style.width = `${percent}%`;
            this.progressText.textContent = `${percent}%`;
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', () => {
        new VideoProcessor();
    });
</script>
</body>
</html>
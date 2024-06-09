# Image Classification Project

Этот проект предоставляет веб-интерфейс для загрузки изображений и их классификации с использованием модели ONNX.

## Установка

1. Клонируйте репозиторий:

    ```bash
    git clone https://github.com/hellsinger1337/image_classification_node.js.git
    cd image_classification_node.js
    ```

2. Установите зависимости:

    - Установите [Node.js](https://nodejs.org/) и необходимые пакеты:

        ```bash
        npm install onnxruntime-node jimp
        ```

    - Убедитесь, что у вас установлен [PHP](https://www.php.net/).

## Запуск

### Запуск веб-интерфейса

1. Запустите PHP сервер:

    ```bash
    php -S localhost:8000
    ```

2. Откройте браузер и перейдите по адресу [http://localhost:8000/index.php](http://localhost:8000/index.php).

### Использование `predict.js`

Если вы хотите использовать `predict.js` для классификации изображений напрямую через командную строку, выполните следующие шаги:

1. Убедитесь, что модель ONNX (`model.onnx`) находится в директории `models`.

2. Выполните команду для классификации изображения:

    ```bash
    node predict.js путь/к/изображению.jpg
    ```

    Результат будет выведен в командной строке.

## Структура проекта

- `index.php`: Главная страница с формой для загрузки изображений и отображением результата классификации.
- `predict.php`: Скрипт для обработки загруженного изображения, выполнения классификации с использованием модели ONNX и отображения результата.
- `predict.js`: Скрипт Node.js для выполнения предсказания на основе модели ONNX.

## Использование веб-интерфейса

1. Откройте главную страницу [http://localhost:8000/index.php](http://localhost:8000/index.php).
2. Нажмите кнопку "Choose Image" для выбора изображения с вашего устройства.
3. Нажмите кнопку "Upload Image" для загрузки изображения и выполнения классификации.
4. Результат классификации будет отображен на той же странице под формой загрузки.

const ort = require('onnxruntime-node');
const Jimp = require('jimp');

const modelPath = './models/model.onnx';

async function preprocessImage(imagePath) {
    const image = await Jimp.read(imagePath);
    image.resize(256, 256); // Изменение размера до 256x256
    const imageData = image.bitmap.data;

    // Конвертация изображения в формат тензора
    const float32Data = new Float32Array(256 * 256 * 3);
    let idx = 0;
    for (let i = 0; i < imageData.length; i += 4) {
        float32Data[idx++] = imageData[i] / 255.0;       // R
        float32Data[idx++] = imageData[i + 1] / 255.0;   // G
        float32Data[idx++] = imageData[i + 2] / 255.0;   // B
    }

    return float32Data;
}

// Функция для выполнения предсказания
async function predict(imagePath) {
    const session = await ort.InferenceSession.create(modelPath);

    const float32Data = await preprocessImage(imagePath);

    // Создание тензора
    const tensor = new ort.Tensor('float32', float32Data, [1, 256, 256, 3]); // Обновленные размеры

    // Создание входного тензора с правильным именем
    const feeds = {};
    feeds['input_layer'] = tensor;

    // Выполнение модели

    const results = await session.run(feeds);
    const output = results['output_0'];

    // Поиск класса с максимальной вероятностью
    const predictedClass = output.data.indexOf(Math.max(...output.data));
    return predictedClass;
}

// Получение пути к изображению из аргументов командной строки
const imagePath = process.argv[2];

if (!imagePath) {
    console.error('Usage: node predict.js <image_path>');
    process.exit(1);
}

predict(imagePath).then(prediction => {
    console.log(prediction);
}).catch(err => {
    console.error(err);
    process.exit(1);
});
const ort = require('onnxruntime-node');
const Jimp = require('jimp');

const modelPath = './models/model.onnx';

async function preprocessImage(imagePath) {
    const image = await Jimp.read(imagePath);
    image.resize(256, 256); 
    const imageData = image.bitmap.data;

    const float32Data = new Float32Array(256 * 256 * 3);
    let idx = 0;
    for (let i = 0; i < imageData.length; i += 4) {
        float32Data[idx++] = imageData[i] / 255.0;       
        float32Data[idx++] = imageData[i + 1] / 255.0;  
        float32Data[idx++] = imageData[i + 2] / 255.0;  
    }

    return float32Data;
}

async function predict(imagePath) {
    const session = await ort.InferenceSession.create(modelPath);

    const float32Data = await preprocessImage(imagePath);

    const tensor = new ort.Tensor('float32', float32Data, [1, 256, 256, 3]); // Обновленные размеры

    const feeds = {};
    feeds['input_layer'] = tensor;


    const results = await session.run(feeds);
    const output = results['output_0'];

    const predictedClass = output.data.indexOf(Math.max(...output.data));
    return predictedClass;
}

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
const ort = require('onnxruntime-node');

async function printModelInfo(modelPath) {
    const session = await ort.InferenceSession.create(modelPath);
    const inputNames = session.inputNames;
    const outputNames = session.outputNames;

    console.log('Input names:', inputNames);
    console.log('Output names:', outputNames);
}

printModelInfo('models/model.onnx').catch(err => {
    console.error(err);
});
<template>
    <div class="mb-16">

        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="setMode('drawing')">ペン</button>
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="setMode('eraser')">消しゴム</button>
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" @click="clearDrawingLayer">クリア</button> <!-- 描画レイヤーをクリア -->

        <div>
            <input type="number" name="" id="" v-model="penWidth">
        </div>
    </div>

    <div>
        <input type="color" name="" id="" v-model="penColor">
    </div>




    <div>
        <input type="file" @change="onFileChange" />
        <div ref="stageContainer" style="width: 600px; height: 400px; border: 1px solid black;"></div>
        <button @click="saveAnnotatedImage">Save Image</button>
    </div>

</template>

<script setup>
import { ref, onMounted } from 'vue';
import Konva from 'konva';
import axios from 'axios';

const stageContainer = ref(null);
let stage;
let layer; // 画像を表示するレイヤー
let drawingLayer; // 描画を表示するレイヤー
let isDrawing = false;
const mode = ref(''); // 現在のモード（ペンまたは消しゴム）
// ペンの太さ
const penWidth = ref(2);
// ペンの色
const penColor = ref('#000000');


const onFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const imageObj = new Image();
            imageObj.src = e.target.result;
            imageObj.onload = () => {
                // ステージのサイズを画像に合わせて調整
                const containerWidth = stageContainer.value.offsetWidth;
                const containerHeight = stageContainer.value.offsetHeight;
                const scale = Math.min(containerWidth / imageObj.width, containerHeight / imageObj.height);
                const newWidth = imageObj.width * scale;
                const newHeight = imageObj.height * scale;

                stage.width(newWidth);
                stage.height(newHeight);

                // 画像を中央に配置
                const konvaImage = new Konva.Image({
                    x: (newWidth - imageObj.width * scale) / 2,
                    y: (newHeight - imageObj.height * scale) / 2,
                    image: imageObj,
                    width: imageObj.width * scale,
                    height: imageObj.height * scale,
                });

                layer.destroyChildren(); // 既存の画像を削除
                layer.add(konvaImage);
                layer.draw();

                // 描画レイヤーもリセット
                drawingLayer.destroyChildren();
                drawingLayer.draw();
            };
        };
        reader.readAsDataURL(file);
    }
};


const saveAnnotatedImage = () => {
    const dataURL = stage.toDataURL();

    axios.post(route('paint_image.store'), { image: dataURL })
    .then(res => {
        console.log(res.data);
    })
    .catch(err => {
        console.log(err);
    });
};

const startDrawing = (e) => {
    if (mode.value === '') return; // モードが無効の場合は何もしない

    isDrawing = true;
    const pos = stage.getPointerPosition();
    const line = new Konva.Line({
        stroke: mode.value === 'eraser' ? 'rgba(255, 255, 255, 1)' : penColor.value, // 消しゴムモードの場合は白色
        strokeWidth: penWidth.value, // 幅を太くして消しやすく
        lineCap: 'round',
        globalCompositeOperation: mode.value === 'eraser' ? 'destination-out' : 'source-over',
        points: [pos.x, pos.y],
    });
    drawingLayer.add(line);
};

const draw = (e) => {
    if (!isDrawing) return;
    const pos = stage.getPointerPosition();

    const lines = drawingLayer.getChildren();
    const lastLine = lines[lines.length - 1];

    lastLine.points(lastLine.points().concat([pos.x, pos.y]));
    drawingLayer.batchDraw();
};

const endDrawing = () => {
    isDrawing = false;
};

const clearDrawingLayer = () => {
    drawingLayer.destroyChildren(); // 描画レイヤー内の全てのオブジェクトを削除
    drawingLayer.draw(); // 描画レイヤーを再描画
};

const setMode = (newMode) => {
    mode.value = newMode;
};

onMounted(() => {
    stage = new Konva.Stage({
        container: stageContainer.value,
        width: 600,
        height: 400,
    });
    
    layer = new Konva.Layer(); // 画像レイヤー
    stage.add(layer);

    drawingLayer = new Konva.Layer(); // 描画レイヤー
    stage.add(drawingLayer);

    stage.on('mousedown touchstart', startDrawing);
    stage.on('mousemove touchmove', draw);
    stage.on('mouseup touchend', endDrawing);
});
</script>

<style scoped>
/* 必要に応じてスタイルを追加 */

</style>

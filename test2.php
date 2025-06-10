<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이미지 모자이크</title>
    <style>
        #canvas {
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <canvas id="canvas"></canvas>
    <input type="file" id="upload" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="app.js"></script>
</body>
</html>

<script>
$(document).ready(function () {
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    let img = new Image();
    let drawing = false;
    let mosaicSize = 15; // 모자이크 크기 (픽셀 단위)

    // 이미지 파일 업로드
    $('#upload').on('change', function (e) {
        const reader = new FileReader();
        reader.onload = function (event) {
            img.onload = function () {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
    });

    // 마우스 또는 터치 시작
    $('#canvas').on('mousedown touchstart', function (e) {
        e.preventDefault();
        drawing = true;
        const pos = getMousePos(e);
        applyMosaic(pos.x, pos.y);
    });

    // 마우스 또는 터치 이동
    $('#canvas').on('mousemove touchmove', function (e) {
        if (drawing) {
            const pos = getMousePos(e);
            applyMosaic(pos.x, pos.y);
        }
    });

    // 마우스 또는 터치 종료
    $('#canvas').on('mouseup touchend', function () {
        drawing = false;
    });

    // 마우스 또는 터치 위치 얻기
    function getMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        const x = (e.clientX || e.touches[0].clientX) - rect.left;
        const y = (e.clientY || e.touches[0].clientY) - rect.top;
        return { x, y };
    }

    // 모자이크 적용 함수
    function applyMosaic(x, y) {
        const imageData = ctx.getImageData(x - mosaicSize / 2, y - mosaicSize / 2, mosaicSize, mosaicSize);
        const pixels = imageData.data;
        const color = getAverageColor(pixels);
        
        // 해당 영역에 평균 색상을 적용하여 모자이크 효과 만들기
        ctx.fillStyle = color;
        ctx.fillRect(x - mosaicSize / 2, y - mosaicSize / 2, mosaicSize, mosaicSize);
    }

    // 픽셀의 평균 색상 계산
    function getAverageColor(pixels) {
        let r = 0, g = 0, b = 0;
        const len = pixels.length;
        for (let i = 0; i < len; i += 4) {
            r += pixels[i];
            g += pixels[i + 1];
            b += pixels[i + 2];
        }
        r = Math.floor(r / (len / 4));
        g = Math.floor(g / (len / 4));
        b = Math.floor(b / (len / 4));
        return `rgb(${r}, ${g}, ${b})`;
    }
});

</script>
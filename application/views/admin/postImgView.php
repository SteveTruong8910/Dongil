<style>
    #category{ display: none; }
    #container{ padding: 0px; }        
</style>

<div id="postImgView" class="imgSheet">
    <?php
    foreach ($writeInfo['photos'] as $key => $img) {
        
        // 특정 조건을 만족하면 현재 반복을 건너뜀(유광, 무광 구분) (continue)
        if (
            (empty($img['isGloss']) && $_GET['isGloss'] == 'N') || // 이미지에 'isGloss' 값이 없고, GET 값이 'N'인 경우
            ($_GET['isGloss'] == 'N' && $img['isGloss'] == 'Y') || // GET 값이 'N'인데, 이미지가 'Y'인 경우
            ($_GET['isGloss'] == 'Y' && (!empty($img['isGloss']) && $img['isGloss'] == 'N')) // GET 값이 'Y'인데, 이미지가 'N'인 경우
        ) {
            continue;
        }

        // 이미지 파일 경로 설정
        $path = FCPATH . 'assets/upload/photos/' . $img['onebonFileName'];

        // 이미지 크기 가져오기
        $dimensions = getimagesize($path);
        $width = $dimensions[0];  // 이미지의 가로 길이
        $height = $dimensions[1]; // 이미지의 세로 길이

        // 이미지의 방향 설정 (가로가 길면 'width', 세로가 길면 'height')
        $clsDirection = $width > $height ? 'width' : 'height';

        // 이미지 회전 관련 변수 초기화
        $rotatedImageDataUri = null;

        // 만약 이미지의 가로가 세로보다 크면 90도 회전
        if ($width > $height) {
            // 회전 후의 크기 조정 (가로와 세로를 변경)
            $width = $dimensions[1];
            $height = $dimensions[0];

            // 이미지 MIME 타입 확인
            $mime = $dimensions['mime'];
            $source = null; // 원본 이미지 리소스 변수 초기화

            // MIME 타입에 따라 이미지 리소스 생성
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $source = imagecreatefromjpeg($path);
                    break;
                case 'image/png':
                    $source = imagecreatefrompng($path);
                    break;
                case 'image/gif':
                    $source = imagecreatefromgif($path);
                    break;
            }

            // 유효한 이미지 리소스가 있으면 90도 회전 수행
            if ($source) {
                $rotated = imagerotate($source, 90, 0); // 90도 회전 (배경 색상: 검정)

                // 메모리에 있는 이미지를 Data URI 형식으로 변환
                ob_start(); // 출력 버퍼 시작
                switch ($mime) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        imagejpeg($rotated); // JPEG로 변환
                        break;
                    case 'image/png':
                        imagepng($rotated); // PNG로 변환
                        break;
                    case 'image/gif':
                        imagegif($rotated); // GIF로 변환
                        break;
                }
                $imageData = ob_get_contents(); // 버퍼 내용 가져오기
                ob_end_clean(); // 버퍼 정리

                // Base64 인코딩하여 Data URI 생성
                $rotatedImageDataUri = 'data:' . $mime . ';base64,' . base64_encode($imageData);

                // 메모리 해제
                imagedestroy($source);
                imagedestroy($rotated);
            }
        }

        
        /* 기존이미지 */
        $clsSize = ($height / $width) >= 1.5? 'maxHeight' : 'maxWidth';            
        $clsBox = 'imgBox';
        $clsImg = 'postImg';
        
        if($img['resizeWidth'] == '1200' && $img['resizeHeight'] == '800') {
            /* 크롭이미지 - 1*/  
            $clsSize = '';            
            $clsBox = 'cropImgBox';
            $clsImg = 'cropImg';                    
        }else if($img['resizeHeight'] == '800') {
            /* 크롭이미지 - 2 (현재는 이거만 사용)*/
            $clsSize = '';
            $clsBox = 'cropImgBox2';
            $clsImg = 'cropImg2';
        }
    ?>
        <div class="imgSheet">
            <div class="letterImgWrap height">
                <div class="<?=$clsBox?>">
                    <div style="position: relative;">
                        <img class="<?=$clsImg?> height <?=$clsSize?>" src="<?= $rotatedImageDataUri ?? '/assets/upload/photos/' . $img['onebonFileName'] ?>" />
                        <span class="writeId photo">
                            <?= $writeInfo['normalWriteId'] . 'P' . ($key + 1) ?><?= $writeInfo['subWriteId']?>
                        </span>                        
                    </div>            
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script>
    var initBodyHtml = '';

    function defaultSetup(){        
        window.onafterprint = afterPrintSetup;
        printPage();
    }

    function printPage(){
        window.print();
    }    

    function afterPrintSetup(){
        window.opener.postMessage('downloadComplete', window.location.origin);
//        document.body.innerHTML = initBodyHtml;
        window.close();
    }
    
    defaultSetup();
</script>
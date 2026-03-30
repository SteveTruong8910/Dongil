<style>
	#category { display: none; }
	#container { padding: 0px; }

	html, body {
		margin: 0;
		padding: 0;
		width: 100%;
		height: 100%;
		background: #fff;
	}

	.first-page {
		position: relative;
		width: 100vw;
		height: 100vh;
		margin: 0;
		padding: 0;
		display: flex;
		justify-content: center;
		align-items: center;
		page-break-after: always;
		overflow: hidden;
	}

	.rotate-box {
		transform: rotate(90deg);
		display: flex;
		justify-content: center;
		align-items: center;
		width: 100vh;
		height: 100vw;
		text-align: center;
	}

	.first-page p {
		margin: 0;
		padding: 0;
		white-space: nowrap;
		font-size: 40px;
		font-weight: bold;
	}

	.imgBox, .cropImgBox, .cropImgBox2 {
		display: flex;
		justify-content: center;
		align-items: center;
		width: 100%;
		height: 100vh;
		margin: 0;
		padding: 0;
		overflow: hidden;
		position: relative;
		page-break-after: always;
	}

	.postImg, .cropImg, .cropImg2 {
		width: 100%;
		height: 100%;
		object-fit: contain;
		display: block;
	}

	@media print {
		@page {
			margin: 0 !important;
			size: auto;
		}

		html, body {
			width: 100%;
			height: 100%;
			margin: 0 !important;
			padding: 0 !important;
		}

		/* 2. Ẩn thành phần thừa */
		#category, header, footer, .loading-bar {
			display: none !important;
		}

		.first-page {
			display: flex !important;
			justify-content: center !important;
			align-items: center !important;
			width: 100vw !important;
			height: 100vh !important;
			margin: 0 !important;
			page-break-after: always !important;
			page-break-inside: avoid !important;
		}

		.rotate-box {
			transform: rotate(90deg) !important;
		}

		.imgBox, .cropImgBox, .cropImgBox2 {
			display: block !important;
			position: relative !important;
			width: 100vw !important;
			height: 100vh !important;
			margin: 0 !important;
			padding: 0 !important;
			page-break-after: always !important;
			page-break-inside: avoid !important;
			overflow: hidden !important;
			left: 0;
			top: 0;
		}

		.imgSheet > div:last-child {
			page-break-after: auto !important;
		}

		.postImg, .cropImg, .cropImg2 {
			width: 100% !important;
			height: 100% !important;
			object-fit: cover !important;
			display: block !important;
			position: absolute;
			top: 0;
			left: 0;
			margin: 0 !important;
			padding: 0 !important;
		}
	}
</style>

<div id="postImgView" class="imgSheet">
    <?php
        foreach($list as $writeInfo) {
		?>
			<?php if ($writeInfo['totalPhotoCnt'] >= 1 && $_GET['isGloss'] == 'Y'): ?>
			<div class="content" style="page-break-after: always;">
				<div class="first-page">
					<div class="rotate-box">
						<p><?= $writeInfo['printOrderId'] . ' ' . $writeInfo['mbName'] . ' ' .'P'. $writeInfo['totalPhotoCnt'] ?></p>
					</div>
				</div>
			</div>
			<?php endif; ?>
		<?php
            foreach ($writeInfo['photos'] as $key => $img) {
                // 특정 조건을 만족하면 현재 반복을 건너뜀(무광, 유광 구분) (continue)
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
			<div class="<?=$clsBox?>">
					<img class="<?=$clsImg?> height <?=$clsSize?>" src="<?= $rotatedImageDataUri ?? '/assets/upload/photos/' . $img['onebonFileName'] ?>" />
			</div>
    <?  }
    }
    ?>
</div>

<script>
    // 모든 이미지 요소 선택
    var imgNodes = document.querySelectorAll('img');  

    // 기본 설정을 수행하는 함수
    function defaultSetup() {        
        showLoadingBar(); // 로딩 바 표시
        window.onafterprint = afterPrintSetup; // 인쇄 후 처리 함수 설정
        
        // 이미지 로딩이 완료될 때까지 기다림
        waitForImagesToLoad().then(() => {});        

        // 2초 후 로딩 바를 숨기고 인쇄 실행
        setTimeout(function() {
            hideLoadingBar();
            window.print();
        }, 2000);
    }
    
    // 모든 이미지가 로드될 때까지 기다리는 함수
    function waitForImagesToLoad() {
        return new Promise((resolve) => {
            let loadedImages = 0; // 로드된 이미지 개수 초기화

            // 모든 이미지에 대해 로드 이벤트 리스너 추가
            imgNodes.forEach(function(imgNode) {
                imgNode.addEventListener("load", function() {
                    loadedImages++; // 로드된 이미지 개수 증가
                    
                    // 모든 이미지가 로드되면 Promise 해결
                    if (loadedImages === imgNodes.length) {
                        resolve();
                    }
                });
            });
        });
    }

    // 인쇄 후 실행되는 함수 (창 닫기)
    function afterPrintSetup() {
        window.close();
    }

    // 기본 설정 함수 실행
    defaultSetup();

</script>

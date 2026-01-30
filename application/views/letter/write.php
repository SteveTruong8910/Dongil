<style>
    #ft_menu{ display: none; }
    .through{ text-decoration: line-through; }

    /* #photosList img {
        max-width: 100%;
        height: auto;
        transform: rotate(0deg);
        object-fit: contain;
    } */

    .btnNext {
    position: relative; /* 또는 absolute/fixed 중 상황에 맞게 */
    z-index: 10;
    }

    .newBadge {
        position: absolute;
        top: 1;
        left: 1;
        width: 45px;
        height: auto;
        z-index: 5;
    }
    .newBadge img {
        display: block;
        width: 100%;
        height: auto;
    }

    .no-click {
        pointer-events: none;
        background-color: #f5f5f5; /* 선택 불가 느낌 */
        cursor: not-allowed;
    }
    
    .addrBtn.active {
        background-color: #ccc; /* 눌린 것처럼 회색 */
        color: #333;
        border-color: #999;
    }
    
    #poboxToggleBtn.active {
        background-color: #ccc !important; /* 회색 배경 */
        color: #333;                       /* 글자색도 어둡게 */
        border: 1px solid #aaa;           /* 테두리도 살짝 */
    }

    #libraryPreviewModal {
        display: none;
        position: fixed;
        z-index: 10000;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.85);
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }

    #libraryPreviewModal img {
        max-width: 90%;
        max-height: 90%;
        border-radius: 12px;
        box-shadow: 0 0 10px #000;
    }

	.spinner {
		display: inline-flex;
		flex-direction: column;
		align-items: center;
		width: 30px;
	}

	.arrow {
		background: none;
		border: none;
		font-size: 12px;
		cursor: pointer;
		color: #676767;
		line-height: 1;
		padding: 2px;
	}

	.arrow.up {
		border-left: 3px solid #aaa;
		border-top: 3px solid #aaa;
		transform: rotate(45deg);
	}

	.arrow.down {
		border-left: 3px solid #aaa;
		border-top: 3px solid #aaa;
		transform: rotate(-135deg);
	}

	.arrow:hover {
		color: #333;
	}

	.number {
		font-size: 12px;
		color: #676767;
		margin: 4px 0;
	}


</style>

<!-- CSS -->
<link rel="stylesheet" href="/assets/css/cropper.min.css">  
<link rel="stylesheet" href="/assets/css/fonts.css<?=$this->config->item('ver')?>">  
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<script src="/assets/js/cropper.min.js"></script>
<script src="/assets/js/load-image.all.min.js"></script>
<script src="/assets/js/textarea-caret-position.js"></script>
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$this->config->item('kakaoJsKey')?>&libraries=services"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="/assets/js/nicepay.js<?=$this->config->item('ver')?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<link rel="stylesheet" type="text/css" href="/assets/css/jquery.emojipicker.css?v=1">
<script type="text/javascript" src="/assets/js/jquery.emojis.js"></script>
<link rel="stylesheet" type="text/css" href="/assets/css/jquery.emojipicker.tw.css?v=1">
<script type="text/javascript" src="/assets/js/jquery.emojipicker.js"></script>
    
<div id="letter">
    <div class="progressBox">
        <span class="circle step1 active">
            <span class="circleMent" style="left: -4px;">선택</span>
        </span> 
        <span class="circle step2">
            <span class="circleMent">편지 쓰기</span>
        </span>
        <span class="circle step3">
            <span class="circleMent">사진 선택</span>
        </span>
        <span class="circle step4">
            <span class="circleMent">문서 전송</span>
        </span>
        <span class="circle step5">
            <span class="circleMent">주소지작성</span>
        </span>        
        <span class="circle step6">
            <span class="circleMent">우편 선택</span>
        </span>
        <span class="line"></span>
        <span id="activeLine" class="activeLine"></span>
    </div>
    
    <div id="step-1" class="step" style="padding-bottom: 180px;">
        
        <p class="selectMent">마음에 드는 편지지를 선택해주세요!</p>        
        <p class="letterMent">기본 편지지 3장</p>
        <p class="letterMent">편지지 추가시 1장당 200원 (심플, 테마는 추가시 1장당 300원)</p>        
        <p class="letterMent">🖱️ 편지지 클릭 시 뒷면을 보실 수 있습니다.</p>        
                
        <div class="cateBox">
            <? foreach($cateList as $key => $data){ 
                if($key == 4) break;
            ?>
                <button class="cate <?=$key == 1 ? 'active' : ''?>" onclick="changeCate(<?=$data['idx']?>)" data-cate="<?=$data['idx']?>">#<?=$data['cateName']?></button>
            <? } ?>  
        </div>

        <div class="cateBox cate2Box hide">
            <? foreach($cateList as $key => $data){
                if($key < 4) continue;
            ?>
                <button class="cate cate2" onclick="changeCate(<?=$data['idx']?>)" data-cate="<?=$data['idx']?>">#<?=$data['cateName']?></button>
            <? } ?>
        </div>

        
        <div id="letterWrap" class="letterWrap">
            <? foreach($list as $key => $data){ ?>
            <?php
                $isNew = (strtotime($data['regDate']) >= strtotime('-7 days'));
            ?>
            <div class="letterViewBox" onclick="choiceLetter($(this))" data-dir="front" data-cate="<?=$data['cateIdx']?>" data-idx="<?=$data['idx']?>">
                <? if($isNew){ ?>
                    <div class="newBadge">
                        <img src="/assets/image/new.png" alt="New">
                    </div>
                <? } ?>
                <div class="dirText">앞면</div>
                <div class="letterImgBox">
                    <img src="<?=$data['imgPath']?>"/>
                </div>
                <? if(!empty($data['imgPathBack'])){ ?>
                    <div class="letterImgBox hide">
                        <img src="<?=$data['imgPathBack']?>"/>
                    </div>
                <? } ?>
                <p class="letterName"><?=$data['name']?></p>
                <p class="letterPrice"><?=number_format($data['price'])?>원</p>
            </div>
            
            <? } ?>
        </div>
                
        <button class="btnNext" onclick="goStep(2)" style="bottom: 70px;">선택완료</button>
        <button class="btnNext" onclick="goStep(3)" style="background: #fe63ae;">사진/문서만 보내기</button>
    </div>
    <div id="step-2" class="step hide" style="padding-bottom: 400px;">
        <p id="paperInfo" class="letterMent">✉️ 15cm x 21cm(120g/㎡) 백상지- 미색 백상지<p>
        <p class="letterMent">- 편지 라인수 1장당 최대 <span id="maxLine">**</span>줄 </p>        
        
        <div>                        
            <p class="letterMent" style="margin:10px 0 0 0; color:black; display: flex; align-items: center;">
                <img src="/assets/image/ico_t.png"> 
                <span style="margin-left: 5px;">글꼴 선택</span>
            </p>
            <input type="text" id="selectFontId" 
                value="<?=$this->user['fontKrName']?>" 
                onclick="openFontModal('show')"
                data-font_family="<?=$this->user['fontFamily']?>"
                data-font_name="<?=$this->user['fontName']?>"
                style="font-family: <?=$this->user['fontName']?>"
                readonly/>
        </div>
        
        <p class="total">
            <span style="font-size: 16px;">현재 장수 : <span id="totalCnt">0</span>장</span>
            <button class="btnAddLetter" onclick="addLetterForm()">편지지 추가 +</button>
        </p>
        
        <!--라인간격-->
        <input type="hidden" id="letterSpacing" value="<?=empty($info['letterSpacing'])? '0px' : $info['letterSpacing']?>"/>
        
        <div id="fontSizeBox" class="fontSizeBox hide">
            <div class="fontSize" onclick="setFontSize($(this))" data-size="14.8px">큰글씨</div>
            <div class="fontSize active" onclick="setFontSize($(this))" data-size="12.8px">중간</div>
            <div class="fontSize" onclick="setFontSize($(this))" data-size="10.8px">작은</div>
            <div class="fontSize" onclick="setFontSize($(this))" data-size="8.8px">매우작은</div>
        </div>
        <div id="customBox" class="customBox">
            <div class="tagBox">
                <img src="/assets/image/font.svg" onclick="setFontSizeBox()"/>
                <img id="changeAlign" src="/assets/image/align-left.svg" onclick="changeAlign()" data-align="left"/>
                <span id="changeWeight" onclick="changeWeight()" data-weight="500">얇게</span>
                <input id="color" type="color" id="changeColor" value="<?=($saveIdx > 0 || $tmpSaveIdx > 0)? $info['color'] : '#000000' ?>" onchange="setLetterForm()" />
                <? if(!isIos() && !isAndroid()) { ?>
                    <img id="emojiBtn" src="/assets/image/emoji.svg"/>
                <? } ?>
            </div>
            
            <div>
                <? if(!empty($this->user['idx']) && $saveIdx == 0){ ?> 
                <button class="nextBtn purpleBack" onclick="tmpSaveLetter()" style="background:#fe63ae;">임시저장</button>
                <? } ?>
                <button class="nextBtn purpleBack" onclick="goStep(3)">등록</button>
            </div>
        </div>
        
        <div id="letterBoxWrap">                        
        </div>
    </div>
    <div id="step-3" class="step hide" style="padding-bottom:70px;">
        
        <p class="selectMent">📷사진 (크기 4x6인치)</p>
        <!-- <p class="letterMent">✉️ 150x100mm / 무광-250g 아트지, 유광-270g 포토용지</p> -->
        <p class="letterMent">✉️ 150x100mm / 유광-270g 포토용지</p>

<!--        <p class="letterMent">✔ 인물사진->유광추천 / 그외->무광추천</p>-->
        <!-- <p class="letterMent">(*5장씩 무제한 업로드 가능 / 장당 무광: 300원, 유광: 500원)</p> -->
        <p class="letterMent">(*5장씩 무제한 업로드 가능 / 장당 유광: 500원)</p>
        
        <? if(!empty($this->user['idx']) && $saveIdx == 0){ ?> 
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button class="tmpSaveBtn" onclick="tmpSaveLetter()">임시저장</button>
                <span>
                    <!-- <button class="tmpSaveBtn" onclick="changeAllImgType('gloss')" style="border: 1px solid #6879FF; background: #6879FF; color:#fff; padding: 10px 8px;">유광 일괄변경</button> -->
                    <!-- <button class="tmpSaveBtn" onclick="changeAllImgType('noneGloss')" style="border: 1px solid black; background: black; color:#fff; padding: 10px 8px;">무광 일괄변경</button> -->
                </span>
            </div>
        <? } ?>
        
        <input id="photoController" type="file" class="hide" accept=".jpg, .jpeg, .png" multiple>
        <input type="hidden" id="photoType" value=""/>
        <div id="photosList" class="photosList">
            <button id="photoDropZone" class="uploadPhotoBtn" onclick="addPhoto('gloss')" style="position: relative;">                
                <i class="fas fa-plus"></i>
                <? if(!(isIos() || isAndroid())) { ?> 
                    <p style="font-size: 12px; position: absolute; bottom: 14px; padding: 0 10px;">이미지를 해당영역에 드래그 또는 클릭하여 이미지 업로드</p>
                <? } ?>
            </button>
        </div>
        
        <button class="btnNext" onclick="goStep(4)">추가완료</button>
    </div>
    <div id="step-4" class="step hide" style="padding-bottom:70px;">        
        <p class="selectMent">📄문서 전송(A4 사이즈)</p>
        <p class="letterMent">✉️100gsm 백상지(<?=(isIos() || isAndroid())? '*3개씩 업로드 / ' : ''?>장당 흑백 150원, 컬러 300원)</p>        
        <p class="letterMent">✅ 봉투는 대봉투로 전송됩니다</p>
        
        <? if(!empty($this->user['idx']) && $saveIdx == 0){ ?> 
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button class="tmpSaveBtn" onclick="tmpSaveLetter()" style="margin-right: 5px;">임시저장</button>
                <span>
                    <button class="tmpSaveBtn" onclick="changeAllFileColor('color')" style="border: 1px solid #6879FF; background: #6879FF; color:#fff; padding: 10px 8px;">컬러 일괄변경</button>
                    <button class="tmpSaveBtn" onclick="changeAllFileColor('black')" style="border: 1px solid black; background: black; color:#fff; padding: 10px 8px;">흑백 일괄변경</button>
                </span>
            </div>
        <? } ?>
        <input type="file" id="fileController" class="hide" name="fileUpload" accept="<?=isIos() || isAndroid()? '.pdf' : '.pdf, .jpg, .jpeg, .png'?>" multiple>
        <input type="file" id="imgFileController" class="hide" name="fileUpload" accept=".jpg, .jpeg, .png" multiple>
                
        <? if(isIos() || isAndroid()) { ?>
            <button class="postBtn" onclick="addImageFile()" style="width: 100%; border: 1px solid black; color: #424242; background: unset; margin-bottom: 0px;">
                <i class="fas fa-camera-retro" style="margin-right:5px;"></i>이미지문서 업로드
            </button>
        <? } ?>
        
        <button id="dropZone" class="postBtn" onclick="addFile()" style="border: 1px dashed black; color: #424242; background: unset; width: 100%; padding: 20px;">
            <p><i class="fas fa-file-upload" style="margin-right:5px;"></i>파일문서 업로드</p>
            <p style="font-size:12px"><?=!(isIos() || isAndroid())? '파일을 해당영역에 드래그 또는 ' : ''?>클릭하여 문서 업로드</p>
            <p style="font-size:12px">(<?=!(isIos() || isAndroid())? '이미지 또는' : ''?> .pdf)</p>
        </button>

        <div id="fileList">
        </div>
        <p class="selectMent" style="padding-top:20px;">📚 자료실(반입 불가한 곳이 있을 수 있으니 사전 확인 바랍니다)</p>
        

        <div class="cateBox cate2Box">
            <? foreach($libraryCateList as $key => $data){ ?>
                <button 
                    class="cate cate2 <?=$data['idx'] == $libraryCateIdx ? 'active' : ''?>"
                    onclick="changeLibraryCate(<?=$data['idx']?>)" 
                    data-cate="<?=$data['idx']?>">
                    #<?=$data['cateName']?>
                </button>
            <? } ?>
        </div>

        

        <!-- step-4 섹션에 추가 -->
        <div id="libraryList" class="fileList" style="margin-top: 20px;">
            <!-- 선택한 자료 목록이 여기에 표시됩니다 -->
        </div>
        <div id="libraryWrap" class="libraryWrap">
            <? foreach($libraryList as $key => $data){ ?>
            <?php
                $isNew = (strtotime($data['regDate']) >= strtotime('-7 days'));
            ?>
            <div class="libraryViewBox" onclick="choicelibrary($(this))" data-dir="front" data-cate="<?=$data['cateIdx']?>" data-page-count="<?=$data['pageCount']?>" data-cate-name="<?=$data['cateName']?>" data-file-path="<?=$data['filePath']?>" data-idx="<?=$data['idx']?>">
                <? if($isNew){ ?>
                    <div class="newBadge">
                        <img src="/assets/image/new.png" alt="New">
                    </div>
                <? } ?>
                <div class="dirText" onclick="event.stopPropagation(); previewLibraryImage('<?=$data['thumbUrl']?>')">미리보기</div>
                <div class="libraryImgBox">
                    <img src="<?=$data['thumbPath']?>"/>
                </div>
                <? if(!empty($data['imgPathBack'])){ ?>
                    <div class="libraryImgBox hide">
                        <img src="<?=$data['imgPathBack']?>"/>
                    </div>
                <? } ?>
                <p class="libraryName"><?=$data['title']?></p>
                <p class="libraryPrice"><?=number_format($data['price'])?>원</p>
            </div>
            
            <? } ?>
        </div>
        
        <!-- 미리보기용 모달 -->
        <div id="libraryPreviewModal" style="display:none; position:fixed; z-index:10000; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center;">
            <img id="libraryPreviewImg" src="" style="max-width:90%; max-height:90%; border-radius:10px;" />
        </div>


        <button class="btnNext" onclick="goStep(5)">주소지 작성하기</button>                
    </div>
    <div id="step-5" class="step hide" style="padding-bottom: 80px;">     
        <p class="selectMent" style="text-align: center; margin-top: 40px; margin-bottom: 5px;">주소 정보를 입력해주세요!</p>
        <div style="text-align: center;">
            <? if(!empty($this->user['idx']) && $saveIdx == 0){ ?> 
                <button class="tmpSaveBtn" onclick="tmpSaveLetter()" style="margin-right:5px;">임시저장</button>
            <? } ?>

            <? if(!empty($this->user)) { ?>
                <button class="postBtn" onclick="onPostPopup('show');">
                    <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>
                    주소지 찾기
                </button>
            <? }else{ ?>
                <button class="postBtn" onclick="onPostPopup('show');">
                    <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>
                    발송했던 주소 불러오기
                </button>
            <? } ?>
        </div>
        <div class="postBox">
            <div class="postHeader">
                <p class="selectMent">보내시는 분이 누구신가요?</p>
                <span class="hide">
                    <label for="isSaveSender">이 주소 기억할래요! </label>
                    <input type="checkbox" id="isSaveSender" value="Y" checked>                    
                </span>
                <button class="addrBtn" id="poboxToggleBtn" onclick="togglePobox()">사서함 서비스</button>

            </div>                    
            
            <p class="guide">주소</p>
            <input type="hidden" id="senderZipCode"/>
            <input type="text" id="senderAddr" class="fieldInput" value="<?=$this->user['senderAddr']?>" placeholder="주소" onclick="openDaumPostcode($('#senderZipCode'), $('#senderAddr'), $('#senderAddrDetail'), 1)" readonly/>
            <p class="guide">상세주소</p>
            <input type="text" id="senderAddrDetail" class="fieldInput" value="<?=$this->user['senderAddrDetail']?>" placeholder="상세주소" <?=$this->user['addressIdx']? '' : 'disabled'?>/>
            <p class="guide">이름</p>
            <input type="text" id="senderName" class="fieldInput" value="<?=$this->user['senderName']?>" placeholder="이름"/>
            <p class="guide">전화번호</p>
            <input type="tel" id="senderTel" class="fieldInput" value="<?=$this->user['senderTel']?>" placeholder="전화번호" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>
			<p class="guide" style="display: inline-block; margin-right: 10px;">우편봉투에 보내는 사람의 휴대폰 번호를 함께 표시하시겠습니까?</p>
			<div style="display: inline-block;">
				<label style="padding: 6px; margin-right: 10px;">
					<input type="radio" name="displayPhone" id="displaySenderPhone" value="1" style="margin-right: 5px" <?=($this->user['displaySenderPhone'] === '1') ? 'checked' : ''?>>예
				</label>
				<label>
					<input type="radio" name="displayPhone" id="displaySenderPhone" value="0" style="margin-right: 5px" <?=($this->user['displaySenderPhone'] === '0' || is_null($this->user['displaySenderPhone'])) ? 'checked' : ''?>>아니요
				</label>
			</div>
            <div class="<?=$isLogin == 'Y'? 'hide': ''?>">
                <p class="guide">✅임의의 숫자 4자리(주문조회 및 주소 불러오기시 필요)</p>
                <input type="tel" id="mbPassword" class="fieldInput" value="" placeholder="비밀번호 4자리" maxlength="4"/>
            </div>            
        </div>
        
        <div class="postBox">
            <div class="postHeader">
                <p class="selectMent">받으시는 분이 누구신가요?</p>                                                                   
                <span class="hide">                    
                    <label for="isSaveReceiver">이 주소 기억할래요! </label>
                    <input type="checkbox" id="isSaveReceiver" value="Y" checked>
                </span>
            </div>
            <div class="addrBtnWrap">
                <button class="addrBtn" onclick="openAddrModal('show', 'army')">전국 군대 훈련소</button>
                <button class="addrBtn" onclick="openAddrModal('show', 'prison')">전국 구치소/교도소/소년원</button>
            </div>            
            <p class="guide">주소</p>
            <p class="letterMent">📮<span>사서함인 경우 'XX우체국 사서함'까지만 기입.</span> 사서함 번호는 상세주소에 적어주세요.</p>
            <input type="hidden" id="receiverZipCode"/>
            <input type="text" id="receiverAddr" class="fieldInput" placeholder="주소" value="<?=$this->user['receiverAddr']?>" onclick="openDaumPostcode($('#receiverZipCode'), $('#receiverAddr'), $('#receiverAddrDetail'), 2)" readonly/>
            <p class="guide">상세주소</p>
            <input type="text" id="receiverAddrDetail" class="fieldInput" value="<?=$this->user['receiverAddrDetail']?>" placeholder="상세주소" <?=$this->user['addressIdx']? '' : 'disabled'?>/>
                        
            <p class="guide">이름</p>
            <p class="letterMent" style="margin-bottom:5px;">✅수감번호인 경우 수감번호+이름 입력해주세요.</p>
            <input type="text" id="receiverName" class="fieldInput" value="<?=$this->user['receiverName']?>" placeholder="이름"/>
                        
            <p class="guide">전화번호(선택)</p>
<!--            <p class="letterMent">✅받는 사람이 전화번호가 없을 경우 보내는 사람 전화번호를 입력해주세요.</p>-->
            <input type="tel" id="receiverTel" class="fieldInput" value="<?=$this->user['receiverTel']?>" placeholder="전화번호" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/>            
        </div>
        <? if(!empty($this->user['idx']) && $saveIdx == 0) { ?>
            <div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-around;">
                <div>
                    <input type="checkbox" id="isSaveAddress" value="Y" onclick="toggleisDefaultBox()"/> 
                    <label for="isSaveAddress">주소지에 추가</label>
                </div>
                <div id="isDefaultAddrBox" class="hide">
                    <input type="checkbox" id="isDefaultAddress" value="Y" />            
                    <label for="isDefaultAddress">기본주소지로 설정</label>
                </div>
            </div>
        <? } ?>
        
        <button class="btnNext" onclick="goStep(6)">작성완료</button>
    </div>
    <div id="step-6" class="step hide" style="padding-bottom: 40px;">        
        <p class="title">이제 마지막입니다!</p>
        <p class="title">우편을 선택해주세요!</p>                
        <p class="letterMent">제주 섬지역 2일 추가 소요</p>
        <p class="letterMent">(일반우편 제외모든 우편) 우편물 추적가능</p>                
        
        <? if(!empty($this->user['idx']) && $saveIdx == 0){ ?> 
            <button class="tmpSaveBtn" onclick="tmpSaveLetter()">임시저장</button>
        <? } ?>                
        
        <div class="payBox">
            <? foreach($this->config->item('stamp') as $key => $data){ ?>
                <div class="stampBox" data-stamp="<?=$key?>" onclick="activeStamp($(this));">                    
                    <p class="stampTitle"><?=$data['name']?> <span class="price"><?=number_format($data['price'])?>원</span></p>                    
                    <p class="ment"><?=$data['ment']?></p>                    
                </div>
            <? } ?>
        </div>
        
        <p class="title hide" style="margin-bottom: 20px;">
            <span style="display: block; color: #9d9d9d; font-size: 12px;">(선택날짜 기준으로 발송시작)</span>
            예약 발송을 진행할까요?            
            <input type="radio" id="isReservY" name="isReserv" value="Y" onclick="checkReserv('Y')" > <label for="isReservY">네</label>
            <input type="radio" id="isReservN" name="isReserv" value="N" onclick="checkReserv('N')" checked> <label for="isReservN">아니요</label>
        </p>
        
        <div id="dateBox" class="dateBox hide">
            <input id="reservDate" type="date" value="<?=date('Y-m-d')?>" min="<?=date('Y-m-d')?>" max="<?=date('Y-12-31', strtotime('+1 year')); ?>">
        </div>
                        
        <div class="pointWrap <?=empty($this->user['idx'])? 'hide' : '' ?>">
            <p class="title">포인트를 사용하시겠어요?</p>
            <div class="point">
                <div>
                    <span class="pointBox">P</span>
                    <span class="pointMent">동글 포인트머니</span>            
                </div>
                <div>
                    <span class="pointMent" style="font-size:12px;">보유 <?=number_format($this->user['point'])?>원 <span id="beforePayPointText"></span></span>
                </div>
            </div>

            <div class="payPointBox">                
                <span>사용</span>
                <div>                                        
                    <input type="hidden" id="myPoint" value="<?=((int)$this->user['point']) + (!empty($saveIdx)? (int)$info['payPoint'] : 0) ?>">
                    <input type="tel" id="payPoint" value="<?=!empty($saveIdx)? number_format($info['payPoint']) : 0?>">
                    <button class="deletePoint" onclick="deletePoint()"><i class="fas fa-times"></i></button>
                    <button id="allPayBtn" class="allPayBtn <?=empty($saveIdx) && $this->user['isAllPoint'] == 'Y'? 'active' : ''?>" onclick="useAllPoint('Y')">전액사용</button>                               
                </div>
            </div>

            <div class="chkPointBox">
                <input type="checkbox" id="isAllPoint" value="Y" <?=$this->user['isAllPoint'] == 'Y'? 'checked' : ''?>>
                <label for="isAllPoint">항상 전액사용</label>
            </div>
        </div>
        
        <p class="title" style="margin-top:20px;">결제금액</p>
        
        <div class="priceBox">
            <p style="font-size: 13px; letter-spacing: -1px;">
                편지 - <span id="totalLetterPrice">0</span>원 / 
                사진 - <span id="totalPhotoPrice">0</span>원 / 
                문서 - <span id="totalFilePrice">0</span>원 /
                자료 - <span id="totalLibraryPrice">0</span>원 /
                우편 - <span id="totalStampPrice">0</span>원 
            </p>
                        
            <span style="display:block; margin-top: 6px; font-size: 13px; letter-spacing: -1px;">
                <i class="fas fa-money-check"></i> 총금액 <span id="totalPriceText">10,000</span>원 
                <span class="<?=empty($this->user['idx'])? 'hide' : '' ?>">
                    - 
                    <div class="point" style="display:inline-block; margin: 10px 0;">
                        <div>
                            <span class="pointBox">P</span>                        
                        </div>
                    </div>
                    포인트 <span id="pointPriceText">0</span>원 
                    = <span id="realTotalPriceText">0</span>원
                </span>
            </span>
        </div>                   
        
        <input type="hidden" id="goodsName" value=""/>
        <input type="hidden" id="realTotalPrice" value="0"/>
        
        <p class="title" style="margin-top: 20px; margin-bottom: 10px;">
            결제타입
            <span style="color: #6a6a6a; font-size: 12px; font-weight: 500;">(✅ 발송완료시 - 현금 3%, 카드 1% 포인트 지급)</span>
        </p>
                      
        <div id="depositBox" class="hide" style="margin-bottom:10px;">
            <p class="letterMent" style="color: #260606;">💸무통장 입금 계좌 안내</p>
            <? foreach($this->config->item('donglDeposit') as $bankNumber => $bank){ ?>            
                <p class="letterMent" onclick="copyToText($(this))" data-bank_number="<?=$bankNumber?>" style="text-decoration: underline; cursor: pointer;">
                    <?=$bank?> <?=$bankNumber?> <?=$this->config->item('donglDepositName')?>
                </p>
            <? } ?>
        </div>
        <div id="depositInfoBox" class="<?=$this->user['isCashReceipt'] == 'N'? 'hide' : ''?>">
            <input type="checkbox" id="isCashReceipt" value="Y" onclick="setIsCashReceipt()" style="margin-bottom:15px;" <?=$this->user['isCashReceipt'] == 'Y'? 'checked' : ''?>> 
            <label for="isCashReceipt">현금영수증 신청</label>
            <div id="cashReceiptDetail" class="hide">
                <div style="margin-bottom: 5px;">
                <? foreach($this->config->item('depositType') as $key => $name){ ?>                
                    <input id="cashReceiptType<?=$key?>" type="radio" name="cashReceiptType" onclick="changeDepositType('<?=$key?>')" value="<?=$key?>"
                           <?=(empty($this->user['cashReceiptType']) && $key == 'earnings') || $key == $this->user['cashReceiptType'] ? 'checked' : ''?>>
                    <label for="cashReceiptType<?=$key?>"><?=$name?></label>
                <? } ?>
                </div>

                <div style="display: flex; margin-bottom:25px; margin-bottom: 10px;">
                    <select id="depositType" style="width: 150px; border: 1px solid black; border-radius: 3px; padding-left: 5px;">
                    <? foreach($this->config->item('depositType2') as $key => $name){    
                        if(($this->user['cashReceiptType'] == 'expenditure' && $key == 'phone') || 
                          ((empty($this->user['cashReceiptType']) || $this->user['cashReceiptType'] == 'earnings') && $key == 'business')) continue;
                    ?>
                        <option value="<?=$key?>" <?=$key == $this->user['depositType']? 'selected' : ''?>><?=$name?></option>
                    <? } ?>
                    </select>
                    <input id="cashReceiptNumber" type="number" placeholder="숫자만 입력해주세요." value="<?=$this->user['cashReceiptNumber']?>"
                           style="width: 100%; border: 1px solid #CDCDCD; border-radius: 5px; height: 40px; margin-left: 10px; padding: 0 10px;"/>                                        
                </div>
                
                <p class="guide">이메일</p>
                <input type="email" id="cashReceiptEmail" class="fieldInput" value="<?=$this->user['cashReceiptEmail']?>" style="margin-bottom: 10px;"/>
                
                <div style="display: flex;">
                    <input id="agreeCashReceipt" type="checkbox" style="<?=!isIos()? 'margin-top: -13px;' : ''?> margin-right: 7px;">
                    <label for="agreeCashReceipt" style="margin-bottom: 15px;">현금영수증 발급을 위하여 휴대폰번호(사업자번호) 또는 현금영수증카드번호 수집에 동의합니다.</label>
                </div>
            </div>
        </div>
        
        <div id="payBtnBox" class="payBtnBox">
        <? foreach($this->config->item('payType') as $key => $name){ 
            if($saveIdx > 0 && !empty($info) && $info['payType'] != $key && $info['payType'] != 'point') continue;
            if($key == 'bank' || $key == 'point') continue;
        ?>
            <button class="payTypeBtn" data-type="<?=$key?>" onclick="setPayBtn($(this));"><?=$name?></button>                
        <? } ?>
        </div>        

<!--        depositType2-->
        <button class="btnPay" onclick="nicepayStart()"><span id="totalPrice">0원 결제하기</span></button>
    </div>       
</div>

<div id="cropperBox" class="hide">
    <div class="imgContainer">
        <div class="containerBox">
            <div class  ="cropperHeader">
                <div style="cursor: pointer;" onclick="closeCropper()"><i class="fas fa-arrow-left"></i></div>
                <span id="cropperBoxTitle">사진편집(자르기)</span>
                <div onclick="removeCropper()" style="cursor: pointer;"><i class="fas fa-trash"></i></div>
            </div>
            <div class="imageBox">
                <div class="cropperSwiper swiper">
                    <div id="photos" class="swiper-wrapper">
                        
                    </div>
                    <div class="swiper-pagination"></div>
                    
                    <!-- If we need navigation buttons -->
<!--
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
-->
                </div>
                <div class="thumbnailSwiper swiper">
                    <div class="swiper-wrapper"></div>
                </div>
            </div>                        
            
            <div class="btnBox">                
                <span id="cropperEvent">
                    <i class="fas fa-sync-alt" onclick="rotateCropper()"></i>
                    <i class="fas fa-search-plus" onclick="upCropper()"></i>
                    <i class="fas fa-search-minus" onclick="downCropper()"></i>
                </span>
                <span id="mosaicEvent" class="hide">
                    <i class="fas fa-magic" onclick="toggleMosaicMode()" id="mosaicToggleBtn"></i>
                    <i class="fas fa-eraser" onclick="mosaicReset()"></i>                    
                </span>
                <button id="finishCropperBtn" class="btnSave" onclick="saveCropper()">편집완료</button>
            </div>
        </div>
    </div>
</div>


<div id="postPopup" class="postPopup popupContainer">
    <div class="popupBox">
        <div class="title">
            <p>주소록<span id="totalPostCnt"></span></p>
            <i class="fas fa-times" onclick="onPostPopup('close');"></i>
        </div>
        
        <div id="postList">
        </div>
    </div>
</div>

<div id="fontModal" class="popupContainer">
    <div class="popupBox">        
        <button class="fontModalcloseBtn" onclick="openFontModal('hide')">닫기</button>        
        <p style="text-align: center;">폰트 변경시 줄 간격 및 편지지 수가 차이날 수 있습니다</p>
        <p style="text-align: center; margin-bottom: 8px;">작성 전 미리 폰트를 선택하는 것을 권장드립니다🙇</p>
        <div class="fontBtnWrap">
            <button id="hand" class="fontBtn active" onclick="changeFontType('hand')">손글씨</button>
        </div>

        <div id="fontList">            
        </div>
    </div>
</div>

<div id="addrModal" class="postPopup popupContainer">
    <div class="popupBox">
        <div class="title">
            <p id="addrTitle">전국 군대 훈련소</p>
            <i class="fas fa-times" onclick="openAddrModal('close');"></i>
        </div>
        
        <select id="postSelect" class="postSelect">
            <option value=""></option>
        </select>
        
        <div id="addrList">
            <p class="postHead"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> 낙생고등학교</p>
            <p class="postContent">주소 - (13480)경기 성남시 분당구 대왕판교로 477</p>
            <p class="postContent">상세주소 - 낙생고등학교</p>
        </div>        
    </div>
</div>

<div id="progressLoader" class="hide">
    <p><span>고객님의 사진</span>을</p>
    <p>인화하고 있어요!</p>
        
    <p class="percent"><span id="percent">0</span> / 100%</p>    
</div>

<script type="text/javascript">
   // 변수 선언
    var isOepnFontModal = false;  // 폰트 모달 열기 상태 (초기값 false)

    // 폰트 관련 설정
    var normalFonts = <?=json_encode($this->config->item('fonts'))?>; // 일반 폰트 목록 (서버에서 전달된 JSON 데이터를 JavaScript 객체로 변환)
    var handFonts = <?=json_encode($this->config->item('handFonts'))?>;  // 손글씨 폰트 목록 (서버에서 전달된 JSON 데이터를 JavaScript 객체로 변환)
    var favoriteFontList = <?=json_encode($favoriteFontList)?>;  // 즐겨찾기 폰트 목록 (서버에서 전달된 JSON 데이터를 JavaScript 객체로 변환)

    // 주소 관련 설정
    const prisonType = <?=json_encode($this->config->item('prisonType'))?>;  // 교도소 종류
    const prison = <?=json_encode($this->config->item('prison'))?>;  // 교도소 목록
    const armyType = <?=json_encode($this->config->item('armyType'))?>;  // 군대 종류
    const army = <?=json_encode($this->config->item('army'))?>;  // 군대 목록

    // 무통장 입금 관련 설정
    const depositTypes = <?=json_encode($this->config->item('depositType2'))?>;  // 무통장 입금 유형 목록

    // 주소 관련 정보
    var addressType = { prison : prisonType, army : armyType };  // prisonType, armyType 설정
    var addressList = { prison : prison, army : army };  // prison, army 설정

    // 로그인 상태 및 사용자 정보
    const isLogin = '<?=$isLogin?>';  // 로그인 여부 (서버에서 전달된 값)
    const memberIdx = <?=$this->user['idx']?>;  // 사용자 ID (서버에서 전달된 값)
    const today = '<?=date('Y-m-d')?>';  // 현재 날짜 (서버에서 전달된 값)

    // 스탬프 및 사진 가격 관련 설정
    var stamps = <?=json_encode($this->config->item('stamp'))?>;  // 스탬프 설정
    const photoPrice = <?=json_encode($this->config->item('photoPrice'))?>;  // 사진 가격 설정

    // 사용자 정보 및 저장 관련 설정
    const info = <?=json_encode($info)?>;  // 사용자 정보
    const saveIdx = <?=(int)$saveIdx?>;  // 저장된 인덱스 (서버에서 전달된 값)
    var tmpSaveIdx = <?=(int)$tmpSaveIdx?>;  // 임시 저장 인덱스 (서버에서 전달된 값)

    // 편지 및 게시물 목록
    var letterList = <?=json_encode($list)?>;  // 편지 목록 (서버에서 전달된 값)
    var postList = [];  // 게시물 목록 (초기값 빈 배열)    

    var libraryIndexes = [];
    var selectedLibraries = [];
    
    // 스텝, 카테고리, 선택된 인덱스 등 다양한 변수 초기화
    var browser = isAndroid()? 'android' : isIos()? 'ios' : 'pc', // 사용 브라우저 저장 함수
        currentStep = 1,  // 현재 단계 (초기값 1)
        cateIdx = 1,  // 카테고리 인덱스 (초기값 1)
        selectIndex = 0,  // 선택된 인덱스 (초기값 0)  
        libraryIndex = -1,  
        letterIndex = -1,  
        isComposing = false, // 한글 입력 중 자소 조합 상태를 추적하기 위한 변수
        lastKeyEvent = null, // 마지막 입력 키 구분 변수        
        autoCallSave = false,  // 자동 저장 여부 (초기값 false)
        aligns = ['left', 'center', 'right'],  // 텍스트 정렬 옵션
        photos = [],  // 사진 목록 (초기값 빈 배열)
        pdfFiles = [],  // PDF 파일 목록 (초기값 빈 배열)
        croppers = [],  // 크롭퍼 목록 (초기값 빈 배열)
        croppedCanvases = [], // 모자이크 크롭퍼 목록(초기값 빈 배열)
        files = [],  // 일반 파일 목록 (초기값 빈 배열)
		libraryFiles = [],

    // 썸네일 swiper 설정
    thumbnailSwiper = new Swiper('.thumbnailSwiper', {            
        slidesPerView: 5,  // 한 번에 보여줄 슬라이드 수
        spaceBetween: 6,  // 슬라이드 간 간격
        watchSlidesProgress: true  // 슬라이드 진행 상황 감지
    }),

    // 크롭퍼 swiper 설정
    cropperSwiper = new Swiper('.cropperSwiper', {            
        pagination: {
            el: '.swiper-pagination',  // 페이지네이션 요소
        },
        navigation: {
            nextEl: '.swiper-button-next',  // 다음 버튼
            prevEl: '.swiper-button-prev',  // 이전 버튼
        },
        allowTouchMove: false,  // 터치 이동 비활성화
        thumbs: {
            swiper: thumbnailSwiper,  // 썸네일 swiper와 연결
        },
    });
    
    /**
     **********************************************
                모든 STEP 공통 함수(START)
     **********************************************
    **/
    
    /* 화면 초기 제일 처음 실행되는 함수 */    
    function defaultSetup() {
        // 모바일이 아닌 경우 이모지 피커 설정
        if(!isMobile()) {
            $('.letterContent').emojiPicker({
                height: '300px', // 이모지 피커의 높이 설정
                width: '450px'   // 이모지 피커의 너비 설정
            });
        }

        // 저장된 인덱스가 있으면 자동으로 편지 저장 호출
        if(saveIdx > 0 || tmpSaveIdx > 0) {            
            autoCallSaveLetter(); // 저장된 편지 불러오기 함수 호출
        }
    }
    
    // Android 기기인지 확인하는 함수
    function isAndroid() {
        const user = navigator.userAgent;
        // 사용자 에이전트에 "Android"가 포함되어 있으면 Android 기기임
        if (user.indexOf("Android") > -1) {
            return true;
        }
        return false;
    }

    // iOS 기기인지 확인하는 함수
    function isIos() {
        const user = navigator.userAgent;
        // 사용자 에이전트에 "iPhone"이 포함되어 있으면 iOS 기기임
        if (user.indexOf("iPhone") > -1) {
            return true;
        }
        return false;
    }

    // 모바일 기기인지 확인하는 함수 (iPhone 또는 Android)
    function isMobile() {
        const user = navigator.userAgent;
        // 사용자 에이전트에 "iPhone" 또는 "Android"가 포함되어 있으면 모바일 기기임
        if (user.indexOf("iPhone") > -1 || user.indexOf("Android") > -1) {
            return true;
        }
        return false;
    }
    
    // 뒤로 가기 버튼을 눌렀을 때 처리하는 함수
    function handlePopState() {
        
        // 만약 이미지 자르기 박스가 열려 있으면 닫고, 스크롤을 다시 활성화
        if (!$('#cropperBox').hasClass('hide')) {
            $('#cropperBox').addClass('hide');
            enableScroll();  // 스크롤 활성화
        } else {
            // 현재 단계가 3이고 letterIndex === -1 이라면 1단계로 이동
            if (currentStep === 3 && letterIndex === -1) {
            goStep(1);
            return;
        }
            // 만약 현재 단계가 1이라면 이전 페이지로 돌아가고,
            // 그렇지 않으면 한 단계 전으로 이동
            if (currentStep == 1) {
                nav.goBack();  // 이전 페이지로 이동
            } else {
                goStep(currentStep - 1);  // 한 단계 전으로 이동
            }
        }
    }
    
    // Android 기기일 경우, 뒤로 가기 이벤트를 처리
    if (isAndroid()) {
        window.onpopstate = handlePopState;  // 뒤로 가기 이벤트 핸들러 등록
    }
    
    /* step 이동 함수 */
    function goStep(step) {

        if (step === 2 && event && event.target && event.target.innerText === "선택완료") {
            if (letterIndex === -1) {
                showAlert('편지지를 선택해주세요.');
                return false;
            }
        }

        history.pushState({ step: step }, "", `#step-${step}`);

        switch(step) {
            case 4:
            case 3: // 3단계와 4단계는 현재 추가적인 체크가 없음
            case 2:
                // 주석 처리된 코드: 편지지가 선택되지 않으면 3단계나 4단계로 넘어갈 수 없도록 처리하는 부분
                // if(letterIndex == -1){
                //     showAlert('편지지를 선택해주세요.');  // 편지지를 선택해달라는 알림을 표시
                //     return false;
                // }
                // if (letterIndex === -1) {
                //     console.log("⚠️ 편지지가 선택되지 않음. step 1로 이동");
                //     showAlert("편지지를 선택해주세요.").then(() => {
                //         goStep(1);
                //     });
                //     return;
                // }
                break;

                break;

            case 5:
                // 5단계에서의 유효성 검사: 편지, 사진, 문서 중 하나라도 등록되어 있는지 확인
                let totalLetterCnt = getLetterTotalCnt();  // 등록된 편지 개수
                let totalPhotoCnt = photos.length;  // 등록된 사진 개수
                let totalPdfFileCnt = pdfFiles.length;  // 등록된 PDF 파일 개수
                totalLibraryCnt = selectedLibraries.length; // 등록된 자료 개수

                // 편지, 사진, 문서 중 하나도 등록되지 않으면 알림을 띄운다
                if (totalLetterCnt == 0 && totalPhotoCnt == 0 && totalPdfFileCnt == 0 && totalLibraryCnt == 0) {
                    showAlert('편지/사진/문서/자료 중 1개 이상은 등록해주세요!');  // 최소 1개 이상의 항목을 등록하라는 알림
                    return;
                }

                // 방송표만 선택한 경우 체크
                if (totalLetterCnt == 0 && totalPhotoCnt == 0 && totalPdfFileCnt == 0 && selectedLibraries.length > 0) {
                    const isOnlyBroadcast = selectedLibraries.every(lib => lib.isBroadcast === true);
                    if (isOnlyBroadcast) {
                        showAlert('방송표만 송부하실 수 없습니다.');
                        return;
                    }
                }

                break;
            case 6:
                // 6단계에서의 유효성 검사: 모든 필수 입력 항목이 채워졌는지 확인
                let $senderAddr = $('#senderAddr'),  // 보내는 사람 주소
                    $senderAddrDetail = $('#senderAddrDetail'),  // 보내는 사람 상세주소
                    $senderName = $('#senderName'),  // 보내는 사람 이름
                    $senderTel = $('#senderTel'),  // 보내는 사람 전화번호
                    $mbPassword = $('#mbPassword'),  // 비밀번호 (비로그인 시)
                    $receiverAddr = $('#receiverAddr'),  // 받는 사람 주소
                    $receiverAddrDetail = $('#receiverAddrDetail'),  // 받는 사람 상세주소
                    $receiverName = $('#receiverName'),  // 받는 사람 이름
					$receiverTel = $('#receiverTel'),
                    falseMsg = '',  // 오류 메시지를 저장할 변수
                    target = null;  // 오류가 발생한 대상 필드

                // 필수 입력 항목들이 비어있는지 하나씩 확인하여 오류 메시지와 해당 필드를 지정
                if (!$senderAddr.val()) {
                    falseMsg = '보내는사람의 주소를 입력해주세요.';  // 보내는 사람 주소가 비어있을 경우 메시지 설정
                    target = $senderAddr;
                } else if (!$senderAddrDetail.val()) {
                    falseMsg = '보내는사람의 상세주소를 입력해주세요.';  // 보내는 사람 상세주소가 비어있을 경우 메시지 설정
                    target = $senderAddrDetail;
                } else if (!$senderName.val()) {
                    falseMsg = '보내는사람의 이름을 입력해주세요.';  // 보내는 사람 이름이 비어있을 경우 메시지 설정
                    target = $senderName;
                } else if (!$senderTel.val()) {
					falseMsg = '보내는사람의 전화번호를 입력해주세요.';  // 보내는 사람 전화번호가 비어있을 경우 메시지 설정
					target = $senderTel;
				} else if ($senderTel.val() && !($senderTel.val().length == 11 || $senderTel.val().length == 10 || $senderTel.val().length == 9)) {
					falseMsg = '전화번호는 숫자  9~11자리로 입력해주세요.';  // 보내는 사람 전화번호가 비어있을 경우 메시지 설정
					target = $senderTel;
                } else if (isLogin == 'N' && $mbPassword.val().length != 4) {
                    falseMsg = '임의의 숫자 4자리를 입력해주세요.';  // 비로그인 상태에서 비밀번호가 4자리가 아닐 경우 메시지 설정
                    target = $mbPassword;
                } else if (!$receiverAddr.val()) {
                    falseMsg = '받는사람의 주소를 입력해주세요.';  // 받는 사람 주소가 비어있을 경우 메시지 설정
                    target = $receiverAddr;
                } else if (!$receiverAddrDetail.val()) {
                    falseMsg = '받는사람의 상세주소를 입력해주세요.';  // 받는 사람 상세주소가 비어있을 경우 메시지 설정
                    target = $receiverAddrDetail;
                } else if (!$receiverName.val()) {
                    falseMsg = '받는사람의 이름을 입력해주세요.';  // 받는 사람 이름이 비어있을 경우 메시지 설정
                    target = $receiverName;
                } else if ($receiverTel.val() && !($receiverTel.val().length == 11 || $receiverTel.val().length == 9 || $receiverTel.val().length == 10)) {
					falseMsg = '전화번호는 숫자  9~11자리로 입력해주세요.';  // 보내는 사람 전화번호가 비어있을 경우 메시지 설정
					target = $receiverTel;
				}
                // } else if (/[^가-힣a-zA-Z0-9\s]/.test($receiverName.val())) {
                //     falseMsg = '받는 사람의 이름에는 특수문자를<br>입력할 수 없습니다.';
                //     target = $receiverName;
                // }


                // 오류가 발생한 필드가 있을 경우 알림을 띄우고, 해당 필드로 포커스를 이동
                if (target != null) {
                    showAlert(falseMsg)  // 오류 메시지 표시
                    .then(() => {                        
                        target.focus();  // 해당 필드로 포커스 이동
                    });
                    return false;
                }                                  
                break;
        }

        // 진행 상태를 나타내는 활성화 바의 너비를 설정 (단계에 따라 진행률 표시)
        let width = 19.9;  // 각 단계마다 진행률을 19.9%씩 증가
        $('#activeLine').css("width", width * (step - 1) + '%');  // 진행 상태를 반영

        // 현재까지 진행한 단계의 원을 활성화 상태로 표시
        for (let n = 0; n <= step; n++) {
            $(`.circle.step${n}`).addClass('active');  // 단계별로 활성화된 원 표시
        }

        // 모든 단계의 내용을 숨기고 현재 단계를 표시
        $('.step').addClass('hide');
        $(`#step-${step}`).removeClass('hide');  // 현재 단계만 보이도록 설정

        // Android에서는 뒤로 가기 버튼을 눌렀을 때 상태가 변경되도록 처리
        if (isAndroid() && currentStep < step) {
            history.pushState(null, null, location.href);  // 현재 URL로 상태를 갱신
        }

        // 로그인 상태에서, 저장된 편지가 없으면 임시 저장
        if (step != 2 && isLogin == 'Y' && saveIdx == 0) {
            tmpSaveLetter(false);  // 임시 저장 호출
        }

        // 페이지 상단으로 스크롤 이동
        $('html, body').scrollTop(0);
        currentStep = step;  // 현재 단계를 업데이트
    }
    
    /**
     * 업로드된 파일들의 총 가격을 계산하는 함수
     * @returns {number} fileTotalPrice - 총 파일 가격
     */
    function getTotalLibraryPrice() {
        let totalLibraryPrice = 0;

        selectedLibraries.forEach(lib => {
            const basePrice = parseInt(lib.price || 0);
            const pageCount = parseInt(lib.pageCount || 0);
			const totalPrice = parseInt(parseInt(lib.libCount) * basePrice);

            // if (lib.isBroadcast) {
            //     // 방송표는 출력비 없음
            //     totalLibraryPrice += basePrice;
            // } else {
            //     // 흑백은 150원, 컬러는 300원
            //     const printFeePerPage = lib.color === 'black' ? 150 : 300;
            //     const totalPrintFee = printFeePerPage * pageCount;

            //     totalLibraryPrice += basePrice + totalPrintFee;
            // }
            totalLibraryPrice += totalPrice;
        });

        return totalLibraryPrice;
    }




    /**
     * 업로드된 파일들의 컬러 정보를 문자열로 반환하는 함수
     * @returns {string} fileColor - 파일 컬러 정보 ('블랙/컬러/블랙' 형태)
     */
    function getFileColor() {
        let $fileWrap = $('.fileWrap').not('.libraryFile'), // 모든 파일 래퍼 요소 가져오기
            fileColor = ''; // 컬러 정보를 저장할 변수 초기화

        // 각 파일 요소를 순회하며 컬러 정보 가져오기
        for (let i = 0; i < $fileWrap.length; i++) {
            let $el = $fileWrap.eq(i), // 현재 파일 요소
                colorName = $el.find('.btnColor.active').hasClass('black') ? '블랙' : '컬러'; // 선택된 색상 확인

            // 파일 컬러 정보를 '/'로 구분하여 문자열로 생성
            fileColor += colorName + (i == $fileWrap.length - 1 ? '' : '/');
        }

        return fileColor; // 컬러 정보 반환
    }

    
    /**
     * 업로드된 전체 파일 개수를 계산하는 함수
     * @returns {number} totalFileCnt - 총 파일 개수
     */
    function getTotalFileCnt() {
        let $fileWrap = $('.fileWrap').not('.libraryFile'), // 모든 파일 래퍼 요소 가져오기
            totalFileCnt = 0; // 총 파일 개수 초기화

        // 각 파일 요소를 순회하며 개수 합산
        for (let i = 0; i < $fileWrap.length; i++) {
            let $el = $fileWrap.eq(i),
                fileCnt = parseInt($el.find('.fileCnt').text()); // 현재 파일 개수 가져오기

            totalFileCnt += fileCnt; // 총 파일 개수에 추가
        }

        return totalFileCnt; // 총 파일 개수 반환
    }

    /**
     * 업로드된 사진들의 총 가격을 계산하는 함수
     * @returns {number} photoTotalPrice - 총 사진 가격
     */
    function getTotalPhotoPrice() {
        let $photoImgBox = $('.photoImgBox'), // 모든 사진 래퍼 요소 가져오기
            photoTotalPrice = 0; // 총 가격 변수 초기화

        // 각 사진 요소를 순회하며 가격 계산
        for (let i = 0; i < $photoImgBox.length; i++) {
            let $el = $photoImgBox.eq(i),
                // isGloss = $el.find('.imgType.active').hasClass('gloss'), // 선택된 사진 타입 확인 (유광/무광)
                // price = isGloss ? 500 : 300; // 유광(500원) 또는 무광(300원) 가격 설정
                // 나중에 변경 필요
                price = 500;

            photoTotalPrice += price; // 총 가격에 추가
        }

        return photoTotalPrice; // 계산된 총 사진 가격 반환
    }
    
    /**
     * 입력된 편지 내용의 총 개수를 계산하는 함수
     * @returns {number} - 입력된 편지 내용의 총 개수
     */
    function getLetterTotalCnt() {
        // .letterContent 클래스의 요소 개수 가져오기
        let letterContentCnt = parseInt($('.letterContent').length),
            letterTotalCnt = 0;

        // 각 편지 내용에 대해 값이 있는지 체크하여 총 개수 계산
        for (let i = 0; i < letterContentCnt; i++) {
            // 공백을 제외한 내용을 확인하여 값이 있을 경우 letterTotalCnt 증가
            if (removeInvisibleCharacters($('.letterContent').eq(i).val())) {
                letterTotalCnt++;
            }
        }

        // 계산된 총 편지 내용 개수 반환
        return letterTotalCnt;
    }

    /* 자료실 가격 계산 함수 */
    // function getTotalLibraryPrice() {
    //     let sum = 0;
        
    //     selectedLibraries.forEach(lib => {
    //         console.log(lib);
    //         // 방송표 카테고리인 경우 무료
    //         if (lib.isBroadcast) {
    //             sum += 0;
    //         } else {
    //             // 흑백/컬러에 따라 가격 계산
    //             if (lib.color === 'black') {
    //                 sum += lib.pageCount * 150; // 흑백: 장당 150원
    //             } else {
    //                 sum += lib.pageCount * 300; // 컬러: 장당 300원
    //             }
    //         }
    //     });
        
    //     return sum;
    // }


    function getFileTotalPrice() {
        let $fileWrap = $('.fileWrap').not('.libraryFile'), // 모든 파일 래퍼 요소 가져오기
            fileTotalPrice = 0; // 총 가격 변수 초기화

        // 각 파일 요소를 순회하며 가격 계산
        for (let i = 0; i < $fileWrap.length; i++) {
            let $el = $fileWrap.eq(i), // 현재 파일 요소
                fileCnt = parseInt($el.find('.fileCnt').text()), // 파일 개수 가져오기
                fileColor = $el.find('.btnColor.active').hasClass('black') ? 'black' : 'color', // 선택된 색상 확인
                price = 0;

            // 흑백(150원) 또는 컬러(300원)에 따라 가격 계산
            price = fileCnt * (fileColor == 'black' ? 150 : 300);

            // 총 가격에 추가
            fileTotalPrice += price;
        }

        return fileTotalPrice; // 계산된 총 파일 가격 반환
    }

    
    /**
     * 가격 정보를 계산하여 화면에 표시하는 함수
     */
    function setPriceInfo() {
        // 현재 선택된 편지 개수
        let letterCnt = getLetterTotalCnt(),

        // 선택된 편지 가격 계산
        totalLetterPrice = letterIndex == -1 ? 0 : ((letterCnt > 0 ? parseInt(letterList[letterIndex]['price']) : 0) + (letterCnt > 3 ? ((cateIdx == 1 ? 200 : 300) * (letterCnt - 3)) : 0)),

        // 사진 가격 계산
        totalPhotoPrice = getTotalPhotoPrice(),

        // 파일 가격 계산
        totalFilePrice = getFileTotalPrice(),

        // 자료 가격 계산
        totalLibraryPrice = getTotalLibraryPrice(),

        // 우표 가격 계산 (선택된 우표가 없으면 0)
        totalStampPrice = parseInt(!$('.stampBox.active').length ? 0 : (stamps[$('.stampBox.active').data('stamp')]['price'])),

        // 전체 가격 계산
        totalPrice = parseInt(totalLetterPrice + totalPhotoPrice + totalFilePrice + totalStampPrice + totalLibraryPrice);

        console.log('totalLetterPrice', totalLetterPrice);
        console.log('totalPhotoPrice', totalPhotoPrice);
        console.log('totalFilePrice', totalFilePrice);
        console.log('totalLibraryPrice', totalLibraryPrice);
        console.log('totalStampPrice', totalStampPrice);
        console.log('realTotalPrice', realTotalPrice);
        console.log('합계:', totalPrice);

        // 가격 정보 화면에 표시
        $('#totalLetterPrice').text(comma(totalLetterPrice));
        $('#totalPhotoPrice').text(comma(totalPhotoPrice));
        $('#totalFilePrice').text(comma(totalFilePrice));
        $('#totalLibraryPrice').text(comma(totalLibraryPrice));
        $('#totalStampPrice').text(comma(totalStampPrice));
        $('#realTotalPrice').val(totalPrice);

        // 포인트 사용 초기화
        useAllPoint('N');

        if (saveIdx > 0 && info.useAllPoint === 'Y') {
            useAllPoint('Y');
        }
        

        // 포인트 값 가져오기
        let point = 0;
        if ($('#payPoint').val()) {
            point = parseInt(uncomma($('#payPoint').val())); // 포인트가 입력되었으면 해당 값 파싱
        }

        // 총 가격과 포인트 차감 후 결제 금액 계산
        let $totalPrice = $('#totalPrice'),
            totalPriceText = comma(totalPrice - point) + `원 결제하기`;

        // 전체 금액, 포인트, 실제 결제 금액 표시
        $('#totalPriceText').text(comma(totalPrice));
        $('#pointPriceText').text(comma(point));
        $('#realTotalPriceText').text(comma(totalPrice - point));

        // 포인트로 결제할 경우, 결제 버튼 숨기기
        if (totalPrice - point == 0) {
            totalPriceText = `${comma(totalPrice)}원 포인트로 결제하기`;
            $('#depositBox, #payBtnBox').addClass('hide');
        } else {
            $('#payBtnBox').removeClass('hide');
        }

        // 저장된 주문 정보가 있으면 이전 결제 정보를 표시
        if (saveIdx > 0) {
            if (info.payType == 'point') {
                let resultPay = totalPrice - point;
                if (resultPay == 0 && info.payPoint == point) {
                    resultText = '수정하기';
                } else {
                    let resultPayText = resultPay == 0 ? `${comma(point)}원 포인트 재결제하기` : `${comma(resultPay)}원 재결제하기`;
                    resultText = `<span class="through">${comma(info.totalPrice)}원(자동취소)</span> ${resultPayText}`;
                }
                $totalPrice.html(resultText);
            } else {
                if (info.payPoint == point && info.totalPrice == totalPrice) {
                    $totalPrice.text("수정하기");
                } else if (info.payType != 'deposit') {
                    let lastMoney = totalPrice - point == 0 ? `${comma(point)}원 포인트로 결제하기` : `${comma(totalPrice - point)}원 재결제하기`;
                    $totalPrice.html(`<span class="through">${comma(info.totalPrice)}원(자동취소)</span> ${lastMoney}`);
                } else {
                    totalPriceText = totalPrice - point == 0 ? `포인트로 결제하기` : comma(totalPrice - point) + `원 변경하기`;
                    $totalPrice.text(totalPriceText);
                }
            }
        } else {
            // 저장된 주문 정보가 없으면 일반적으로 총 가격 표시
            $totalPrice.text(totalPriceText);
        }
    }
    
    /**
     * 문자열 끝에 있는 공백을 제거하는 함수
     * @param {string} str - 공백을 제거할 문자열
     * @returns {string} - 끝 공백이 제거된 문자열
     */
    function trimEndOnly(str) {
        return str.replace(/\s+$/, '');  // 정규식을 사용하여 문자열 끝에 있는 공백을 제거
    }

   function removeInvisibleCharacters(str) {
	   return str.replace(/[\s\u200B\u200C\u200D\u2060\uFEFF]/g, '');
   }

    /* 임시저장 함수 */
    async function tmpSaveLetter(isResultModal = true) {        
        let $payType = $('.payTypeBtn.active'),  // 활성화된 결제 타입 버튼을 가져옴
            payType = '';  // 결제 타입을 초기화

        // 결제 타입이 설정된 버튼이 있으면 해당 타입을 가져옴
        if ($payType.length) {
            payType = $payType.data('type');
        }        

        // 편지 정보를 임시 저장하는 함수 호출
        let res = await setWrite(payType, 'Y', isResultModal);

        // 임시 저장 결과가 실패하면 에러 메시지 표시
        if (!res.result) {
            showAlert(res.msg);
            return;  // 함수 종료
        }

        // 임시 저장된 편지의 인덱스를 저장
        tmpSaveIdx = res.writeIdx;

        // 결과 모달을 표시해야 하면 임시 저장 완료 메시지 표시
        if (isResultModal) {
            showAlert('임시저장 되었습니다<br>임시저장된 편지지는 마이페이지에서 확인 가능합니다.');
        }
    }
    
    /**
     * 편지 정보를 서버로 전송하여 저장하는 함수
     * @param {boolean} isLoader - 로딩 상태를 표시할지 여부
     * @returns {Object} - 서버 응답 결과
    */
    async function setWrite(payType, isTmpSave = 'N', isLoader = true){
        let data = letterIndex != -1 ? letterList[letterIndex] : {},  // 편지 데이터
            letterIdx = letterIndex == -1 ? -1 : data['idx'],  // 편지 인덱스
            productName = letterIndex == -1 ? '사진/문서' : data['name'],  // 상품명
            fontName = $('#selectFontId').attr('data-font_name'),  // 폰트 이름
            fontFamily = $('#selectFontId').attr('data-font_family'),  // 폰트 패밀리
            fontKrName = $('#selectFontId').val(),  // 폰트 한글 이름
            fontSize = $('.fontSize.active').attr('data-size'),  // 폰트 크기
            fontAlign = $('#changeAlign').attr('data-align'),  // 정렬 방식
            fontWeight = $('#changeWeight').attr('data-weight'),  // 폰트 두께
            totalLetterCnt = getLetterTotalCnt(),  // 편지 총 장수
            content = [],  // 편지 내용 배열
            totalPhotoCnt = photos.length,  // 사진 총 개수
            totalPdfFileCnt = getTotalFileCnt(),  // PDF 파일 총 개수
            senderAddr = $('#senderAddr').val(),  // 보내는 사람 주소
            senderAddrDetail = $('#senderAddrDetail').val(),  // 보내는 사람 상세주소
            senderName = $('#senderName').val(),  // 보내는 사람 이름
            senderTel = $('#senderTel').val(),  // 보내는 사람 전화번호
            receiverAddr = $('#receiverAddr').val(),  // 받는 사람 주소
            receiverAddrDetail = $('#receiverAddrDetail').val(),  // 받는 사람 상세주소
            receiverName = $('#receiverName').val(),  // 받는 사람 이름
            receiverTel = $('#receiverTel').val(),  // 받는 사람 전화번호
            stamp = parseInt($('.stampBox.active').data('stamp')),  // 우표 종류
            deliveryDate = $('#isReservY').is(':checked') ? $('#reservDate').val() : today,  // 배송일
            mbName = senderName,  // 회원 이름
            mbPhoneNumber = senderTel,  // 회원 전화번호
            mbPassword = $('#mbPassword').val(),  // 비밀번호
            letterPrice = letterIndex == -1 ? 0 : ((totalLetterCnt > 0 ? parseInt(letterList[letterIndex]['price']) : 0) + (totalLetterCnt > 3 ? ((cateIdx == 1 ? 200 : 300) * (totalLetterCnt - 3)) : 0)),  // 편지 가격
            photosPrice = getTotalPhotoPrice(),  // 사진 가격
            filePrice = getFileTotalPrice(),  // 파일 가격
            libraryPrice = getTotalLibraryPrice(),  // 자료실 가격 (추가)
            stampPrice = 0,  // 우표 가격 초기화
            totalPrice = 0,  // 총 가격 초기화
            payPoint = 0,  // 사용 포인트
            realTotalPrice = 0,  // 실제 결제 금액
            isCashReceipt = $('#isCashReceipt').is(':checked')? 'Y' : 'N', // 현금영수증 여부
            cashReceiptType = $('input[name=cashReceiptType]:checked').val(), // 현금영수증 유형
            depositType = $("#depositType option:selected").val(), // 입금 유형
            cashReceiptNumber = $('#cashReceiptNumber').val(), // 현금영수증 번호
            cashReceiptEmail = $('#cashReceiptEmail').val(), // 현금영수증 이메일
			displaySenderPhone = $('input[name="displayPhone"]:checked').val();


        // 현금영수증 유효성 검사
        if(payType == 'deposit' && isTmpSave != 'Y' && isCashReceipt == 'Y') {                                   
            if(!cashReceiptNumber) {
                return {result: false, msg: `${$("#depositType option:selected").text()}에 대한 번호를 입력해주세요`};   
            }else if(!$('#agreeCashReceipt').is(':checked')) {
                return {result: false, msg: `현금영수증 발급을 위하여 휴대폰번호(사업자번호) 또는 현금영수증카드번호 수집에 동의해주세요`};
            }else if(!validateEmail(cashReceiptEmail)) {
                return {result: false, msg: '이메일 형식에 맞게 입력해주세요'};
            }
        }
        
        // 편지, 사진, 문서, 자료 중 하나라도 없으면 에러
        if (isTmpSave == 'N' && totalLetterCnt == 0 && totalPhotoCnt == 0 && totalPdfFileCnt == 0 && selectedLibraries.length == 0) {
            return {result: false, msg: '편지/사진/문서/자료 중 1개 이상은 등록해주세요!'};
        }

        let onlyBroadcastSelected = selectedLibraries.length > 0 && selectedLibraries.every(lib => lib.isBroadcast === true);

        // 방송표만 선택한 경우 체크
        if (isTmpSave == 'N' && totalLetterCnt == 0 && totalPhotoCnt == 0 && totalPdfFileCnt == 0 && onlyBroadcastSelected) {
            return {result: false, msg: '방송표만 송부하실 수 없습니다.'};
        }

        // 우표 가격 설정
        if (!isNaN(stamp)) {
            stampPrice = stamps[stamp]['price'];
        } else {
            stamp = -1;
        }

        // 총 가격 계산 (자료실 가격 추가)
        totalPrice = parseInt(letterPrice + photosPrice + filePrice + libraryPrice + stampPrice);
        payPoint = parseInt(uncomma($('#payPoint').val()));
        realTotalPrice = totalPrice - payPoint;

        // 최소 결제 금액 체크
        if (isTmpSave == 'N' && payType != 'point' && realTotalPrice < 100) {
            return {result: false, msg: '카드/페이의 최소결제금액은 100원입니다.'};
        }

        // 상품명과 실제 결제 금액 설정
        $('#goodsName').val(productName);
        $('#realTotalPrice').val(realTotalPrice);

        // 편지 내용 설정
        for (let i = 0; i < $('.letterContent').length; i++) {
            let value = trimEndOnly($('.letterContent').eq(i).val());
            if (value) {
                content.push(value);
            }
        }

        // 사진 유무광 설정
        for (let i = 0; i < photos.length; i++) {
            photos[i].isGloss = 'Y'; // 유광으로 고정
        }

        console.log('selectedLibraries', selectedLibraries);
        
        // 자료실 항목에서 필요한 데이터 추출
        let libraryFiles = selectedLibraries.map(lib => {
            let realFileName = '';
            if (lib.filePath) {
                const pathParts = lib.filePath.split('/');
                realFileName = pathParts[pathParts.length - 1];
            }

            return {
                libraryIdx: lib.idx,
                ext: 'pdf',
                fileName: realFileName,
                originalFileName: lib.name + '.pdf',
                pageCount: lib.pageCount || 1,
				libCount: lib.libCount || 1,
            };
        });




        let libraryFileColors = selectedLibraries.map(lib => lib.color); // 색상만 따로


        const setWriteRes = await postJson('/userApi/setWrite', {
            isLogin : isLogin,
            saveIdx : saveIdx,
            isTmpSave: isTmpSave,
            tmpSaveIdx : tmpSaveIdx,
            memberIdx : memberIdx,
            payType : payType,
            letterIdx : letterIdx,
            productName : productName,
            fontName : fontName,
            fontFamily : fontFamily,
            fontKrName : fontKrName,
            fontSize : fontSize,
            letterSpacing : $('#letterSpacing').val(),
            fontAlign : fontAlign,
            fontWeight : fontWeight,
            color : $('#color').val(),
            totalLetterCnt : totalLetterCnt,
            content : content,
            totalPhotoCnt : totalPhotoCnt,
            photos : photos,
            totalPdfFileCnt : totalPdfFileCnt,
            pdfFiles : pdfFiles,
            fileColor : getFileColor(),
            senderAddr : senderAddr,
            senderAddrDetail : senderAddrDetail,
            senderName : senderName,
            senderTel : senderTel,
            isSaveSender : $('#isSaveSender').is(':checked')? 'Y' : 'N',
            receiverAddr : receiverAddr,
            receiverAddrDetail : receiverAddrDetail,
            receiverName : receiverName,
            receiverTel : receiverTel,
            isSaveReceiver : $('#isSaveReceiver').is(':checked')? 'Y' : 'N',
            stamp : stamp,
            deliveryDate : deliveryDate,
            mbName : mbName,
            mbPhoneNumber : mbPhoneNumber,
            mbPassword : mbPassword,
            isAllPoint : $('#isAllPoint').is(':checked')? 'Y' : 'N',
            letterPrice : letterPrice,
            photoPrice : photosPrice,
            filePrice : filePrice,
            stampPrice : stampPrice,
            totalPrice : totalPrice,
            payPoint : payPoint,
            realTotalPrice : realTotalPrice,
            isSaveAddress : $('#isSaveAddress').is(':checked')? 'Y' : 'N',
            isDefaultAddress : $('#isDefaultAddress').is(':checked')? 'Y' : 'N',
            isCashReceipt : isCashReceipt,
            cashReceiptType : cashReceiptType,
            depositType : depositType,
            cashReceiptNumber : cashReceiptNumber,
            cashReceiptEmail : cashReceiptEmail,
            // 자료실 정보 추가
            libraryFiles: libraryFiles,
            libraryFileColor: libraryFileColors.join('/'),
            totalLibraryFileCnt: selectedLibraries.length,
            libraryPrice: libraryPrice,
			displaySenderPhone: displaySenderPhone
        }, isLoader);

        return setWriteRes;
    }
    
    /**
     * 계좌번호를 클립보드에 복사하는 함수
     * @param {jQuery} $this - 클릭된 요소 (계좌번호가 담긴 데이터가 있는 요소)
     */
    function copyToText($this) {
        // 클릭된 요소에서 'bank_number' 데이터를 가져옴
        var bankNumber = $this.data('bank_number');

        /**
         * 주어진 텍스트를 클립보드에 복사하는 함수
         * @param {string} text - 클립보드에 복사할 텍스트
         */
        function copyToClipboard(text) {
            // 복사할 텍스트를 담을 임시 input 요소 생성
            var tempInput = document.createElement('input');
            tempInput.style.position = 'absolute';
            tempInput.style.left = '-9999px';  // 화면에 보이지 않도록 설정
            tempInput.value = text;  // 텍스트 값 설정
            document.body.appendChild(tempInput);  // DOM에 추가
            tempInput.select();  // 입력 필드 선택
            document.execCommand('copy');  // 클립보드에 복사
            document.body.removeChild(tempInput);  // DOM에서 제거

            // 클립보드 복사 완료 후 알림 표시
            showAlert('계좌번호가 복사되었습니다.<br>' + text);
        }

        // 복사할 계좌번호를 클립보드에 복사
        copyToClipboard(bankNumber);
    }        
    
    /**
     * 특정 텍스트 영역에 대한 동작을 처리하는 함수
     * @param {jQuery} $textarea - 처리할 텍스트 영역 요소
     */
    function actionInput($textarea) {
        // 현재 텍스트 영역이 포함된 '.addForm'의 인덱스를 가져옴
        let index = $textarea.closest('.addForm').index();

        // 해당 인덱스에 맞는 '.letterContent' 요소에 'input' 이벤트를 트리거
        // 이때, 'isFocus' 값은 false로 설정
        $('.letterContent').eq(index).trigger('input', { isFocus : false });
    }

    /**
     * 주어진 텍스트 영역의 위치에 따라 페이지를 스크롤하는 함수
     * @param {jQuery} textarea - 스크롤할 텍스트 영역 요소
     * @param {number} index - 텍스트 영역의 인덱스
     * @param {Object} cursorPosition - 커서의 위치 정보 (top, left 값 포함)
     */
    function setTop(textarea, index, cursorPosition) {
        // 텍스트 영역의 top 위치 값을 가져옴
        let topOffset = textarea.offset().top,
            topset = 0;  // 기본 오프셋 값 설정
        
        // topset 값을 수정하여 스크롤 위치를 변경
        if(isMobile()) {
            topset = -80;    
        }else {
            topset = -150;
        }                

        // 텍스트 영역의 상단 위치와 커서 위치를 합산하여 페이지를 스크롤
        // 스크롤을 상단과 좌측으로 이동
        $('html, body').scrollTop(topOffset + topset + cursorPosition.top)
                       .scrollLeft(cursorPosition.left - 100);                
    }

    
    
    /**
     **********************************************
                모든 STEP 공통 함수(END)
     **********************************************
    **/
    
    /**
     **********************************************
            임시 저장 및 수정시 처음 로드(START)
     **********************************************
    **/
    
    /* STEP_1 저장 편지지 선택 */
    function autoCallSaveLetter() {
        autoCallSave = true;  // 자동 저장 여부를 true로 설정

        // 편지 인덱스가 -1인 경우 (편지가 저장되지 않은 경우)
        if(info.letterIdx == -1) {
            setPriceInfo();  // 가격 정보 설정
            autoCallSaveLetterDefault();  // 기본 편지지 설정 함수 호출
            goStep(3);  // 3단계로 이동
            autoCallSave = false;  // 자동 저장 여부를 false로 설정
        } else {
            // 저장된 편지 인덱스가 있을 경우, 해당 카테고리로 변경
            changeCate(parseInt(info.cateIdx));

            // 특정 편지에 해당하는 요소가 로드될 때까지 대기
            const intervalId = setInterval(function() {
                const elementCount = $(`.letterViewBox[data-idx="${info.letterIdx}"]`).length;

                showLoadingBar();  // 로딩 바 표시

                // 해당 요소가 로드되었을 때
                if (elementCount >= 1) {
                    // 해당 편지 클릭 2번 (앞면 보여주기 위해 2번클릭)
                    $(`.letterViewBox[data-idx="${info.letterIdx}"]`).click().click();
                    clearInterval(intervalId);  // setInterval 종료
                    autoCallSaveLetter2();  // 저장된 편지 불러오기 함수 호출
                    hideLoadingBar();  // 로딩 바 숨기기
                    goStep(2);  // 2단계로 이동
                    autoCallSave = false;  // 자동 저장 여부를 false로 설정
                }
            }, 300);  // 0.3초마다 실행
        }
    }
    
    /* STEP_2 저장 편지지 입력 */
    function autoCallSaveLetter2() {
        setFontSizeBox();  // 폰트 크기 박스를 설정하는 함수 호출
        $(`.fontSize[data-size="${info.fontSize}"]`).click();  // 서버에서 전달받은 폰트 크기로 해당 폰트 크기 버튼 클릭

        // 폰트 굵기가 '900'이면 굵기 변경
        if(info.fontWeight == '900') {
            changeWeight();  // 폰트 굵기를 설정하는 함수 호출
        }

        // 서버에서 전달받은 내용(info.content)을 순차적으로 처리
        for(let i = 0; i < info.content.length; i++) {                        
            addLetterForm();  // 편지 입력 폼을 추가하는 함수 호출
            $('.letterContent').eq(i).val(info.content[i]);  // 각 입력 폼에 내용 추가
        }        

        // 폰트를 설정하는 함수 호출
        choiceFont(info.fontKrName, info.fontName, info.fontFamily);        

        autoCallSaveLetterDefault();  // 기본 편지지 설정 함수 호출
    }        
    
    /* STEP_3,4,5,6 저장 사진, 문서, 주소, 우편 입력 */
    function autoCallSaveLetterDefault() {
        /* 사진 */
        photos = info.photos;  // 저장된 사진 목록을 photos 변수에 할당
        setReadPhoto(info.photos);  // 사진을 읽어와서 표시하는 함수 호출

        /* 문서 */
        pdfFiles = info.pdfFiles;  // 저장된 PDF 파일 목록을 pdfFiles 변수에 할당
        setReadFile(info.pdfFiles, '', info.fileColor);  // 파일을 읽어와서 표시하는 함수 호출

		libraryFiles = info.libraryFiles;
		setLibraryFiles(libraryFiles);

        /* 주소 */
        $("#senderAddr").val(info.senderAddr);  // 보내는 사람의 주소를 설정
        $("#senderAddrDetail").val(info.senderAddrDetail).attr('disabled', false);  // 보내는 사람의 상세 주소 설정, disabled 속성 제거
        $("#senderName").val(info.senderName);  // 보내는 사람의 이름 설정
        $("#senderTel").val(info.senderTel);  // 보내는 사람의 전화번호 설정
        $('#mbPassword').val(info.mbPassword);  // 멤버 비밀번호 설정
        $("#receiverAddr").val(info.receiverAddr);  // 받는 사람의 주소 설정
        $("#receiverAddrDetail").val(info.receiverAddrDetail).attr('disabled', false);  // 받는 사람의 상세 주소 설정, disabled 속성 제거
        $("#receiverName").val(info.receiverName);  // 받는 사람의 이름 설정
        $("#receiverTel").val(info.receiverTel);  // 받는 사람의 전화번호 설정
		if (info.displaySenderPhone !== 0) {
			$('input[name="displayPhone"][value="' + info.displaySenderPhone + '"]').prop('checked', true);
		} else {
			$('input[name="displayPhone"][value="0"]').prop('checked', true);
		}


			/* 우편 */
        $(`.stampBox[data-stamp="${info.stamp}"]`).click();  // 선택된 스탬프를 클릭하여 적용
        $('#reservDate').val(info.deliveryDate);  // 배송 예정일 설정
        $(`.payTypeBtn[data-type="${info.payType}"]`).click();  // 결제 방법을 설정

        /* 현금영수증 */
         if(info.isCashReceipt == 'Y') {
             setIsCashReceipt();  // 현금영수증 설정 함수
         }

        /* 포인트 */
        if(saveIdx > 0 && Number(info.payPoint) > 0) {
            $('#beforePayPointText').text(`+ 결제포인트 ${comma(info.payPoint)}원`);  // 결제 포인트 표시
        }
    }
    
    /**
     *******************************************************
                임시 저장 및 수정시 처음 로드(END)
     *******************************************************
    **/
    
    /**
     **********************************************
                    STEP - 1(START)
     **********************************************
    **/
    
    /* 카테고리 변경 */
    async function changeCate(choiceCateIdx){
        // 선택된 카테고리 인덱스를 정수로 변환하여 cateIdx에 할당
        // 이 함수는 사용자가 선택한 카테고리에 따라 해당 카테고리의 편지지 목록을 불러옵니다.
        cateIdx = parseInt(choiceCateIdx);

        /* 테마 선택인 경우 */
        if(cateIdx == 3){
            // 카테고리가 3이면, cate2 항목 중 첫 번째 항목의 카테고리를 선택
            cateIdx = $('.cate2').first().data('cate');
        }

        let sortType = (cateIdx == 1) ? "ASC" : "DESC";

        // 서버에 카테고리 변경 요청을 보내고, 결과를 받아옴
        // 이 부분은 서버로부터 해당 카테고리와 관련된 편지지 목록을 받아옵니다.
        const changeCateRes = await postJson('/userApi/getLetter', {
            cateIdx : cateIdx,
            sortType: sortType
        }, false);

        let list = changeCateRes.list,  // 서버에서 받은 편지지 리스트
            listHtml = "";  // 리스트를 담을 HTML 변수

        // 모든 카테고리 항목에서 active 클래스를 제거
        $('.cate').removeClass('active');
        $('.cate2Box .cate2').removeClass('active');

        // "전체"가 선택되었을 경우의 동작
        if (cateIdx == 0) {
            $('.cate[data-cate="0"]').addClass('active'); // "전체"를 활성화
            $('.cate2Box').addClass('hide'); // 서브 카테고리 숨기기
        } else if (cateIdx >= 3) { // 테마 카테고리일 경우
            $('.cate2Box').removeClass('hide');
            $(`.cate[data-cate="3"]`).addClass('active'); // 메인 테마 카테고리 활성화
            $(`.cate2[data-cate="${cateIdx}"]`).addClass('active'); // 선택된 서브 카테고리 활성화
        } else {
            $('.cate2Box').addClass('hide'); // 서브 카테고리 숨기기
            $(`.cate[data-cate="${cateIdx}"]`).addClass('active'); // 선택된 카테고리 활성화
        }


        // 리스트가 비어 있을 경우 '등록된 편지지가 없습니다.' 메시지를 출력
        if(!list.length){
            listHtml = '<p class="empty">등록된 편지지가 없습니다.</p>';
        }

        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        sevenDaysAgo.setHours(0, 0, 0, 0);

        console.log("7일 전 기준 날짜:", sevenDaysAgo.toISOString());

        // 리스트가 있을 경우, 리스트 항목들을 HTML로 만들어 추가
        for(let i=0; i<list.length; i++){
            let data = list[i],  // 각 편지지 항목 데이터
                imgBackHtml = "",  // 뒷면 이미지 HTML (초기값 빈 문자열)
                viewText = '앞면',  // 기본적으로 '앞면' 텍스트 설정
                clsFront = '',  // 앞면 이미지 클래스 (초기값 빈 문자열)
                clsBack = 'hide',  // 뒷면 이미지 클래스 (초기값 'hide' 클래스)
                dir = 'front';  // 기본적으로 'front' 방향 설정
            
            let regDateStr = data.regDate;
            let regDate = null;

            if (regDateStr) {
                // "YY-MM-DD HH:mm:ss" → "YYYY-MM-DDTHH:mm:ss"로 변환
                let dateParts = regDateStr.split(" ");
                if (dateParts.length === 2) {
                    let ymd = dateParts[0].split("-");
                    let time = dateParts[1];

                    if (ymd.length === 3) {
                        let year = parseInt(ymd[0]) + 2000; // "24" → 2024, "25" → 2025
                        let formattedDate = `${year}-${ymd[1]}-${ymd[2]}T${time}`;

                        regDate = new Date(formattedDate);
                    }
                }
            }

            const sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
            sevenDaysAgo.setHours(0, 0, 0, 0);

            let isNew = regDate && regDate >= sevenDaysAgo;

            console.log(`아이템 ${data.idx} - 변환된 날짜:`, regDate, "| New 여부:", isNew);

            // 선택한 카테고리가 3 이상(테마)인 경우 '뒷면'을 설정
            if(choiceCateIdx >= 3 || choiceCateIdx == 0) {
                viewText = '뒷면';
                clsFront = 'hide';  // 앞면을 숨김
                clsBack = '';  // 뒷면을 보이게 설정
                dir = 'back';  // 방향을 'back'으로 설정
            }

            // 뒷면 이미지가 있을 경우 HTML로 추가
            if(data['imgPathBack']){
                imgBackHtml = `
                    <div class="letterImgBox ${clsBack}">
                        <img src="${data['imgPathBack']}?v=1"/>
                    </div>`;
            }

            // 편지지 항목 HTML을 리스트에 추가
            listHtml += `
                <div class="letterViewBox" onclick="choiceLetter($(this))" data-idx="${data.idx}" data-dir="${dir}">
                    ${isNew ? `<div class="newBadge"><img src="/assets/image/new.png" alt="New"></div>` : ""}
                    <div class="dirText">${viewText}</div>
                    <div class="letterImgBox ${clsFront}">
                        <img src="${data.imgPath}?v=1"/>
                    </div>
                    ${imgBackHtml}
                    <p class="letterName">${data.name}</p>
                    <p class="letterPrice">${comma(data.price)}원</p>
                </div>
            `;
        }

        // 편지지 목록을 최신 데이터로 갱신
        letterList = list;

        // 생성한 HTML을 letterWrap에 삽입
        $('#letterWrap').html(listHtml);
    }
    
    /* 편지 선택 */
    function choiceLetter($this) { 
        let index = $this.index(), // 클릭된 편지지 항목의 인덱스를 가져옴
            $letterViewBox = $('.letterViewBox'), // 모든 편지지 항목을 선택
            $letterImgBox = $this.find('.letterImgBox'), // 선택된 편지지의 이미지 박스
            $dirText = $this.find('.dirText'); // 선택된 편지지의 '앞면'/'뒷면' 텍스트

        letterIndex = index;
        
        let letterIdx = $this.data('idx'); // 편지지 고유 idx
        let paperType = ""; // 용지 타입 초기화

        if (cateIdx == 1 && letterIdx == 222) {
            paperType = "크라프트지";
        } else if (cateIdx == 1) {
            paperType = "미색 백상지";
        } else {
            paperType = "백상지";
        }


        // 문구 업데이트
        $('#paperInfo').html(`✉️ 15cm x 21cm(120g/㎡) ${paperType}`);

        $letterViewBox.removeClass('active'); // 모든 편지지 항목에서 활성화 클래스를 제거
        $this.addClass('active'); // 현재 선택된 편지지 항목에 활성화 클래스를 추가

        // '앞면'과 '뒷면'을 토글하기 위한 조건문
        if ($letterImgBox.length == 2) { 
            $letterImgBox.addClass('hide'); // 이미지 박스를 숨김

            if ($this.attr('data-dir') == 'front') { // '앞면'을 선택했다면
                $this.attr('data-dir', 'back'); // '뒷면'으로 변경
                $letterImgBox.eq(1).removeClass('hide'); // '뒷면' 이미지를 보이게 함
                $dirText.text('뒷면'); // 텍스트를 '뒷면'으로 변경
            } else { // '뒷면'을 선택했다면
                $this.attr('data-dir', 'front'); // '앞면'으로 변경
                $letterImgBox.eq(0).removeClass('hide'); // '앞면' 이미지를 보이게 함
                $dirText.text('앞면'); // 텍스트를 '앞면'으로 변경
            }
        }

        letterIndex = index; // 선택된 편지지의 인덱스를 저장
       
        // if (saveIdx == 0 && tmpSaveIdx == 0 && $('.letterContent').length === 0) {
        //     console.log("✅ 편지지가 없으므로 3개 추가!");
        //     for (let i = 0; i < 3; i++) {
        //         addLetterForm();
        //     }
        // }

        /* 임시저장, 수정 외에는 강제로 3개 추가 */
        if (saveIdx == 0 && tmpSaveIdx == 0) { 
            let maxLetterCnt = 3, // 최대 편지 내용 입력란 개수
                $letterContent = $('.letterContent'); // 편지 내용 입력란

            // 편지 내용 입력란이 없으면 새로운 입력란을 추가
            if (!$letterContent.length) {
                addLetterForm(); // 첫 번째 입력란 추가
                for (let cnt = 1; cnt < maxLetterCnt; cnt++) { // 최대 개수까지 입력란 추가
                    addLetterForm();
                }
            }
        }
        
        setLetterForm(); // 편지 내용 폼을 설정
        setPriceInfo(); // 가격 정보를 설정
    }
    
    /**
     **********************************************
                    STEP - 1(END)
     **********************************************
    **/
    
    
    
    /**
     **********************************************
                    STEP - 2(START)
     **********************************************
    **/
    
    /* 편지지 추가 */
    function addLetterForm() { 
        if(letterIndex == -1) { // 편지지가 선택되지 않은 경우
            showAlert('편지지를 선택해주세요'); // 경고 메시지 표시
            goStep(1); // 첫 번째 단계로 이동
            return;
        }

        let letterLength = $('.letterBox').length, // 현재 추가된 편지지 수
            addLetterForm = ` 
            <div class="addForm">                                 
                <div class="letterBox"> 
                    <p class="letterPage">${Number(letterLength) + 1}페이지</p>
                    <textarea class="letterContent" data-index="${letterLength}"></textarea>
                </div>
            </div>`; 

        $('#totalCnt').text(letterLength + 1); // 전체 페이지 수 업데이트
        $('#letterBoxWrap').append(addLetterForm); // 새로운 편지 폼을 추가

        if(!isMobile()) { 
            // 모바일이 아닌 경우 이모지 피커 추가
            $(".letterContent").eq(letterLength).emojiPicker({
                width: '300px',
                height: '400px',
                button: false
            });
        }  

        setLetterForm(); // 편지지 디자인 설정
        setPriceInfo(); // 가격 정보 업데이트
    }

    function isTooBright(color) {
        const temp = $('<div>').css('color', color).appendTo('body');
        const computedColor = temp.css('color'); // ex: 'rgb(244, 240, 240)'
        temp.remove();

        const match = computedColor.match(/\d+/g);
        if (!match || match.length < 3) return false;

        const r = parseInt(match[0], 10);
        const g = parseInt(match[1], 10);
        const b = parseInt(match[2], 10);

        const brightness = 0.299 * r + 0.587 * g + 0.114 * b;
        return brightness >= 220;
    }

    
    /* 편지지 기본 세팅 */
    function setLetterForm() {
        let data = letterList[letterIndex], // 선택된 편지지 데이터 가져오기
            $letterBox = $('.letterBox'), // 편지지의 전체 박스를 선택
            $letterContent = $('.letterContent'); // 편지 내용 입력란 선택

        $('#maxLine').text(data.maxLine); // 최대 줄 수를 화면에 표시
        $letterBox.css('background-image', `url(${data.imgOnebonPath})`); // 편지지 배경 이미지를 설정
        $letterBox.css('padding-top', data.topPadding + 'px'); // 편지지 상단 패딩을 설정

        // 폰트 크기를 설정 (활성화된 폰트 사이즈로)
        $letterContent.css('font-size', `calc(${$('.fontSize.active').attr('data-size')}`);

        // 폰트 자간 간격 설정 (기존 -0.5px => 0px로 변경)
        $letterContent.css('letter-spacing', `calc(${$('#letterSpacing').val()}`);        
        
        // 텍스트 정렬을 설정 (사용자가 선택한 정렬 방식으로)
        $letterContent.css('text-align', $('#changeAlign').attr('data-align'));

        // 텍스트 두께를 설정 (사용자가 선택한 두께로)
        $letterContent.css('-webkit-text-stroke', $('#changeWeight').attr('data-weight') == '500' ? '0.02em' : '0.06em');

        // 폰트를 설정 (사용자가 선택한 폰트로)
        $letterContent.css('font-family', $('#selectFontId').attr('data-font_family'));

        // 텍스트 영역의 너비를 설정 (편지지에 맞게)
        $letterContent.css('width', data.contextWidth + 'px');

        // 줄 간격을 설정 (편지지에 맞게)
        $letterContent.css('line-height', data.contextLineHeight + 'px');

        // 텍스트 색상을 설정 (사용자가 선택한 색상으로)
        let selectedColor = $('#color').val();
        if (isTooBright(selectedColor)) {
            showAlert('흰색에 가까운 너무 밝은 색상은 사용할 수 없습니다');
            $('#color').val('#000000');
            $letterContent.css('color', '#000000');
        } else {
            $letterContent.css('color', selectedColor);
        }

    }
    
    /* 편지지 삭제 (현재 사용 X) */
    function removeLetterForm($this) {
        $this.closest('.addForm').remove(); // 클릭한 삭제 버튼의 상위 요소인 .addForm을 제거
        $('#totalCnt').text($('.letterBox').length); // 현재 남아있는 letterBox 수를 전체 페이지 수로 표시
        setPriceInfo(); // 가격 정보 업데이트
    }
    
    /* 첫 번째 편지 내용 입력란에 input 이벤트 트리거 */
    function setAllTriger() {
        $('.letterContent').eq(0).trigger('input', { isFocus: false }); 
    }
    
    /* 폰트 크기 선택 박스를 숨기거나 표시 */
    function setFontSizeBox() {
        $('#fontSizeBox').toggleClass('hide');
    }
        
    /* 폰트사이즈 변경 */
    function setFontSize($this) {
        $('.fontSize').removeClass('active'); // 모든 폰트 크기 옵션에서 active 클래스 제거
        $this.addClass('active'); // 클릭한 폰트 크기 옵션에 active 클래스 추가
        setFontSizeBox(); // 폰트 크기 선택 박스를 토글
        setLetterForm(); // 편지지 폼을 업데이트

        if (!autoCallSave) { // 자동 저장이 아닌 경우
            setAllTriger(); // 첫 번째 텍스트 영역에 'input' 이벤트 트리거
        }
    }    
    
    /* 폰트선택창 토글 */
    function openFontModal(type) {
        let $fontModal = $('#fontModal'); // 폰트 모달 엘리먼트 선택

        if (type == 'show') { // 모달을 열 때
            if (!isOepnFontModal) { // 모달이 아직 열리지 않은 경우
                isOepnFontModal = true; // 모달 열림 상태로 설정
                setupFontList(); // 폰트 리스트 설정
            }
            $('html, body').css('overflow', 'hidden'); // 페이지 스크롤을 비활성화
            $fontModal.addClass('show'); // 모달에 'show' 클래스를 추가하여 모달 표시
        } else { // 모달을 닫을 때
            $('html, body').css('overflow', ''); // 페이지 스크롤을 다시 활성화
            $fontModal.removeClass('show'); // 모달에서 'show' 클래스를 제거하여 모달 숨김
        }
    }
    
    /* 폰트리스트 출력 */
    function setupFontList() {
        let list = '', // 폰트 리스트를 담을 변수
            type = $('#normal').hasClass('active') ? 'normal' : 'hand', // 'normal' 혹은 'hand' 타입 확인
            typefonts = type == 'normal' ? normalFonts : handFonts, // 타입에 따라 폰트 리스트 선택
            sortedTypefonts = typefonts
                .filter(font => favoriteFontList.some(favorite => favorite.fontName === font.fontName)) // 즐겨찾기 폰트를 먼저 배치
                .concat(typefonts.filter(font => !favoriteFontList.some(favorite => favorite.fontName === font.fontName))); // 나머지 폰트를 뒤에 배치

        for (let index in sortedTypefonts) { // 정렬된 폰트 리스트 순회
            let data = sortedTypefonts[index],
                isStarActive = favoriteFontList.some(font => font.fontName === data.fontName) ? 'active' : 'noActive', // 즐겨찾기 여부 체크
                starHtml = '';

            // 로그인된 상태에서만 별 아이콘 표시
            if (isLogin == 'Y') {
                starHtml = `
                    <i class="far fa-star"></i>
                    <i class="fas fa-star"></i>
                `;
            }

            // 폰트 목록에 HTML 추가
            list += `<div class="fontWrap">
                        <div class="starWrap ${isStarActive}" onclick="onFavoriteFont($(this), '${type}', '${data['fontName']}', '${data['font-family']}')">
                            ${starHtml}
                            <span>
                                <img src="/assets/fonts/view/${data['fontName']}.png?v=2" style="height: 30px; margin-left: 5px;"/>
                            </span>
                        </div>

                        <div class="defaultWrap">
                            <a class="checkBtn" onclick="choiceFont('${data['name']}', '${data['fontName']}', '${data['font-family']}')">선택</a>
                        </div>
                    </div>`;
        }

        $('#fontList').html(list); // 생성된 폰트 리스트를 #fontList 요소에 추가
    }
    
    /* 일반 또는 손글씨로 변경 */
    function changeFontType(id) {
        $('.fontBtn').removeClass('active'); // 모든 폰트 버튼에서 active 클래스 제거
        $(`#${id}`).addClass('active'); // 클릭한 버튼에 active 클래스 추가
        setupFontList(); // 폰트 리스트를 새로 설정
    }
    
    /* 폰트 선택 */
    async function choiceFont(name, fontName, fontFamily) {
        // 폰트 파일을 link 태그로 동적으로 추가
        let fontLink = $(`<link rel="stylesheet" data-font href="/assets/fonts/${fontName}.css?v=20250220">`);

        fontLink.on('load', async function() {            
            await document.fonts.ready; // 폰트가 사용할 준비가 됐다고 알려주는 Promise
            
            // 🔹 특정 폰트가 실제로 로드되었는지 확인
            try {
                // 16px 크기로 폰트 로드 확인
                await document.fonts.load(`16px ${fontName}`);

				let fontWeight = 'normal';
				if (fontName === 'GangwonEduModoo_Bold') {
					fontWeight = 'bold';
				}

                // 로드가 완료되면 폰트를 .letterContent에 적용
				$('.letterContent').css({
					'font-family': fontFamily,
					'font-weight': fontWeight
				});

                // 폰트 정보를 selectFontId에 업데이트
				$('#selectFontId').val(name)
					.attr('data-font_family', fontFamily)
					.attr('data-font_name', fontName)
					.attr('data-font_weight', fontWeight)
					.css({
						'font-family': fontName,
						'font-weight': fontWeight
					});

                // 브라우저에서 폰트 렌더링이 끝난 후에 setAllTriger() 호출
                setTimeout(() => {
                    setAllTriger(); // ✅ 폰트가 완전히 적용된 후 실행
                }, 100); // 렌더링 시간을 주기 위해 100ms 후 실행

                // 폰트 설정 모달을 닫음
                openFontModal('close');
            } catch (error) {
                // 폰트 로드 실패 시 에러 로그
                console.error('폰트 로드 실패:', error);
            }
        });

        // head 태그에 fontLink 추가
        $('head').append(fontLink);
    }
    
    /* 폰트 즐겨찾기 */
    async function onFavoriteFont($this, type, fontName, fontFamily) {
        // 로그인되지 않은 상태에서 실행되지 않도록 체크
        if(isLogin == 'N') return;

        let favoriteFontIdx = 0;

        // 선택된 폰트를 즐겨찾기에서 추가하거나 제거하는 과정
        if($this.hasClass('noActive')) {
            // 폰트를 즐겨찾기에 추가
            $this.removeClass('noActive');
            $this.addClass('active');
        } else {
            // 폰트를 즐겨찾기에서 제거
            favoriteFontIdx = favoriteFontList[favoriteFontList.findIndex(font => fontName === fontName)]['idx'];

            $this.removeClass('active');
            $this.addClass('noActive');   
        }

        // 즐겨찾기 폰트 추가/제거를 서버에 반영
        const favoriteFontRes = await postJson('/userApi/favoriteFont', {
            favoriteFontIdx: favoriteFontIdx,
            fontName: fontName,
            fontFamily: fontFamily
        });

        // 서버에서 받은 최신 즐겨찾기 리스트로 업데이트
        favoriteFontList = favoriteFontRes.list;
    }
    
    /* 텍스트 위치 변경 */
    function changeAlign(){
        // 'data-align' 속성 값을 가져옴
        let $changeAlign = $('#changeAlign'),
            align = $changeAlign.attr('data-align'),
            alignIndex = aligns.indexOf(align) + 1;

        // alignIndex가 2를 초과하면 초기화 (순환)
        if(alignIndex > 2){
            alignIndex = 0;
        }
        
        $changeAlign.attr('data-align', (aligns[alignIndex])); // 새로운 alignment 값으로 'data-align' 속성 업데이트
        $changeAlign.attr('src', `/assets/image/align-${aligns[alignIndex]}.svg`); // 이미지 변경 (새로운 alignment 값에 해당하는 이미지로)
        
        setLetterForm(); // 레터폼 설정 업데이트        
    }

    /* 텍스트 굵기 변경 */
    function changeWeight(){
        let $changeWeight = $('#changeWeight'), // #changeWeight 요소 선택
            weight = $changeWeight.attr('data-weight'); // 현재 텍스트의 굵기 정보 가져오기 (500 또는 900)
        
        $changeWeight.attr('data-weight', weight == '500'? '900': '500'); // 텍스트 굵기를 500에서 900으로, 또는 900에서 500으로 변경
        $changeWeight.text(weight == '500'? '굵게': '얇게'); // 버튼의 텍스트를 '굵게' 또는 '얇게'로 변경
        setLetterForm(); // 텍스트 스타일을 업데이트
    }
    
    /* 화면 포커스가 풀렸을 때 이벤트 */
    function letterBlurEvent() {
        // 숨겨둔 헤더와 커스텀 박스를 다시 보이게 함
        $('#hd_wrapper, #customBox').removeClass('hide');

        // 뷰포트를 원래 크기로 되돌림 (확대 불가)
        document.getElementById('viewport').setAttribute('content', 'width=device-width, initial-scale=1.0, user-scalable=1, maximum-scale=1');

        // 로그인된 상태에서 글을 저장해야 하는 경우 임시 저장 호출
        if(isLogin == 'Y' && saveIdx == 0){
            tmpSaveLetter(false);
        }
    }
    
    /**
     **********************************************
                    STEP - 2(END)
     **********************************************
    **/
    
    
    /**
     **********************************************
                    STEP - 3(START)
     **********************************************
    **/
    
    // 사진 업로드 유형을 설정하고 업로드 버튼을 클릭하는 함수
    function addPhoto(type) {
        $('#photoType').val(type); // 선택된 사진 유형을 설정
        $('#photoController').click(); // 사진 업로드 컨트롤러 클릭 (업로드 창 열기)
    }
    
    // 사진 파일을 읽고 리사이징한 후, Croper.js를 사용하여 이미지 크롭을 설정하는 비동기 함수
    async function readPhotoImg(input) {
        let imgFileTypes = ['jpg', 'jpeg', 'png']; // 업로드 가능한 이미지 파일 확장자 설정

        if (!input.files.length) return; // 파일이 없으면 종료
        if (input.files.length > 5) { // 최대 5장까지 업로드 가능
            input.value = ''; // 파일 입력 초기화
            showAlert('사진은 5장씩 업로드 가능합니다.'); // 경고 메시지 표시
            return;
        }

        // 기존 슬라이드 초기화
        cropperSwiper.removeAllSlides();
        cropperSwiper.update();
        thumbnailSwiper.removeAllSlides();
        thumbnailSwiper.update();

        files = []; // 업로드된 파일 목록 초기화
        croppers = []; // Cropper 인스턴스 초기화

        let cnt = 0,
            inputCnt = input.files.length; // 입력된 파일 개수

        showLoadingBar(); // 로딩 바 표시
        $('#cropperBox').removeClass('hide'); // 크롭퍼 박스 표시
        $('#cropperBoxTitle').text('사진편집(자르기)');
        $('#finishCropperBtn').attr('onclick', 'saveCropper()'); // 크롭퍼 저장모드로 변경
        $('#cropperEvent').removeClass('hide');
        $('#mosaicEvent').addClass('hide');
        
        disableScroll(); // 스크롤 비활성화

        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i]; // 파일 정보
            let extension = file.name.split('.').pop().toLowerCase(); // 파일 확장자
            let isImage = imgFileTypes.includes(extension); // 이미지 파일인지 확인

            if (!isImage) { // 이미지 파일이 아니면 경고 표시
                showAlert('이미지 파일만 업로드 가능합니다.');
                $('#cropperBox').addClass('hide');
                hideLoadingBar();
                return;
            }

            let reader = new FileReader(); // 파일 읽기 객체
            reader.onload = async function (e) {
                let resizedDataUrl = await resizeImage(e.target.result); // 리사이징된 이미지 URL 생성

                let cropperImgId = `cropper${i}`; // 크롭퍼 이미지 ID 설정

                // Swiper 슬라이드에 리사이징된 이미지 추가
                cropperSwiper.appendSlide(
                    `<div class="swiper-slide">
                        <img id="${cropperImgId}" src="${resizedDataUrl}" />
                    </div>`
                );

                thumbnailSwiper.appendSlide(
                    `<div class="swiper-slide">
                        <img src="${resizedDataUrl}"/>
                    </div>`
                );

                // 이미지가 로드된 후 Cropper 적용
                let cropperImg = document.getElementById(cropperImgId);
                cropperImg.onload = function () {
                    croppers.push(new Cropper(cropperImg, {
                        dragMode: 'move', // 이동 모드 설정
                        aspectRatio: cropperImg.width >= cropperImg.height ? 5.84 / 4 : 4 / 5.84, // 이미지 비율 설정
                        autoCropArea: 0.9, // 자동 크롭 영역 설정
                        restore: false, // 복원 기능 비활성화
                        guides: false, // 가이드라인 비활성화
                        center: false, // 중앙 정렬 비활성화
                        highlight: false, // 강조 표시 비활성화
                        cropBoxMovable: false, // 크롭 박스 이동 비활성화
                        cropBoxResizable: false, // 크롭 박스 크기 조정 비활성화
                        toggleDragModeOnDblclick: false, // 더블 클릭 시 드래그 모드 전환 비활성화
                        ready: function () {
                            this.cropper.zoom(-0.1); // 크롭퍼 초기화 시 줌 아웃
                        }
                    }));

                    files.push(file); // 업로드된 파일을 배열에 추가
                };
                cnt++; // 파일 처리 카운트 증가

                if (cnt == inputCnt) { // 모든 파일을 처리한 후
                    hideLoadingBar(); // 로딩 바 숨기기
                    if (isAndroid()) { // Android일 경우 히스토리 추가
                        history.pushState(null, null, location.href);
                    }
                }
            };

            reader.readAsDataURL(file); // 파일을 Data URL 형식으로 읽기
        }
        input.value = ''; // 파일 입력 초기화
    }

    // 이미지 리사이징 함수
    // function resizeImage(dataUrl) {
    //     return new Promise((resolve) => {
    //         let img = new Image();
    //         img.src = dataUrl;

    //         img.onload = function () {
    //             let canvas = document.createElement("canvas");
    //             let ctx = canvas.getContext("2d");

    //             let maxSize = 1600; // 최대 크기 설정
    //             let width = img.naturalWidth;
    //             let height = img.naturalHeight;

    //             if (width > height) {
    //                 // 🔥 가로 사진이면 90도 회전해서 세로로 만듦
    //                 canvas.width = height;
    //                 canvas.height = width;
    //                 ctx.translate(height, 0);
    //                 ctx.rotate(90 * Math.PI / 180);
    //             } else {
    //                 // 세로 사진은 회전 없이 그대로 사용
    //                 canvas.width = width;
    //                 canvas.height = height;
    //             }

    //             ctx.drawImage(img, 0, 0, width, height);
    //             let resizedDataUrl = canvas.toDataURL("image/jpeg", 1.0);
    //             resolve(resizedDataUrl);
    //         };
    //     });
    // }
    function resizeImage(dataUrl) {
        return new Promise((resolve) => {
            const img = new Image();
            img.src = dataUrl;

            img.onload = function () {
            let maxSize = 1600; // 최대 해상도
            let width = img.naturalWidth;
            let height = img.naturalHeight;

            // 📏 해상도 비율 유지하면서 줄이기
            if (width > maxSize || height > maxSize) {
                if (width > height) {
                height *= maxSize / width;
                width = maxSize;
                } else {
                width *= maxSize / height;
                height = maxSize;
                }
            }

            const canvas = document.createElement("canvas");
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, width, height);

            // 📉 JPEG 품질도 조정 가능 (0.7 정도로 줄이면 용량 크게 줄어듦)
            const resizedDataUrl = canvas.toDataURL("image/jpeg", 0.8);
            resolve(resizedDataUrl);
            };
        });
    }


    
    // 이미지 출력
    function setReadPhoto(files, photoType = '') {
        let photosHtml = '';

        for (let i = 0; i < files.length; i++) {
            let data = files[i],
                clsNoneGlossActive = 'active',
                clsGlossActive = '';

            // 만약 photoType이 없고, isGloss가 정의되지 않았다면, 기본값을 'Y'로 설정
            if (!photoType && data.isGloss == undefined) data.isGloss = 'Y';

            // 유광 또는 무광에 따라 클래스 변경
            if (data.isGloss == 'Y' || photoType == 'gloss') {
                clsNoneGlossActive = '';
                clsGlossActive = 'active';
            }

            // 사진을 표시하는 HTML 생성
            photosHtml += `
                <div class="photoImgBox">
                    <i class="fas fa-times" onclick="removeImg($(this))"></i>
                    <img src="/assets/upload/photos/${data.fileName}"/>
                    <div class="btnBox btnImgBox">
                        <!-- <button onclick="changeImgType($(this))" class="imgType gloss ${clsGlossActive}">유광</button> -->
                        <!-- <button onclick="changeImgType($(this))" class="imgType noneGloss ${clsNoneGlossActive}">무광</button> -->
                    </div>
                </div>
            `;
        }

        // 생성된 HTML을 #photosList에 추가
        $('#photosList').append(photosHtml);        
        setPriceInfo();  // 가격 정보 업데이트
    }

    // 모든 이미지의 타입을 변경하는 함수
    function changeAllImgType(type) {        
        let $imgType = $(`.imgType.${type}`);

        // 모든 이미지 버튼에 대해 변경
        for (let i = 0; i < $imgType.length; i++) {
            let $target = $imgType.eq(i);            
            changeImgType($target);  // 각각의 버튼에 대해 이미지 타입 변경
        }
    }

    // 단일 이미지 타입을 변경하는 함수
    function changeImgType($this) {
        // 같은 부모 안에 있는 모든 버튼에서 'active' 클래스를 제거
        $this.parent().children('.imgType').removeClass('active');
        // 현재 클릭한 버튼에 'active' 클래스 추가
        $this.addClass('active');
        setPriceInfo();  // 가격 정보 업데이트
    }

    // 이미지를 삭제하는 함수
    function removeImg($this) {
        let $target = $this.closest('.photoImgBox'),
            index = $target.index() - 1;

        // 해당 이미지 삭제
        $target.remove();
        photos.splice(index, 1);  // 배열에서 해당 파일도 삭제

        setPriceInfo();  // 가격 정보 업데이트
    }

    // 크로퍼 박스를 닫는 함수
    function closeCropper() {
        $('#ft_menu').removeClass('hide');  // 메뉴를 보이게
        $('#cropperBox').addClass('hide');  // 크로퍼 박스를 숨김
        enableScroll();  // 스크롤 활성화
    }
    
    // 이미지 크로퍼에서 현재 이미지를 삭제하는 함수
    function removeCropper() {
        // 삭제 여부 확인을 위한 확인 팝업 표시
        showConfirm('해당 이미지를 삭제하시겠습니까?')
        .then(async (result) => {
            // 사용자가 삭제를 선택하지 않으면 반환
            if (!result.value) return;

            let index = cropperSwiper.activeIndex;  // 현재 활성화된 슬라이드의 인덱스를 가져옴

            // 크로퍼 및 썸네일에서 해당 슬라이드 삭제
            cropperSwiper.removeSlide(index);
            thumbnailSwiper.removeSlide(index);

            // 파일 배열과 크로퍼 배열에서 해당 항목 삭제
            files.splice(index, 1);
            croppers.splice(index, 1);

            // 크로퍼가 더 이상 없다면 크로퍼 박스를 닫음
            if (!croppers.length) {
                closeCropper();
            }
        });
    }

    // 크로퍼에서 현재 이미지를 확대하는 함수
    function upCropper() {
        // 현재 활성화된 크로퍼의 확대 비율을 0.1만큼 증가
        croppers[cropperSwiper.activeIndex].zoom(0.1);
    }

    // 크로퍼에서 현재 이미지를 축소하는 함수
    function downCropper() {
        // 현재 활성화된 크로퍼의 확대 비율을 0.1만큼 감소
        croppers[cropperSwiper.activeIndex].zoom(-0.1);
    }

    // 크로퍼에서 현재 이미지를 90도 회전하는 함수
    function rotateCropper() {
        // 현재 활성화된 크로퍼의 이미지를 90도 회전
        croppers[cropperSwiper.activeIndex].rotate(90);
    }        

    // 이미지 자르기 후 모자이크 모드로 이동
    async function saveCropper() {
        croppedCanvases = [];
        for (let i = 0; i < croppers.length; i++) {
            let naturalWidth = croppers[i].canvas.width;  // 원본 캔버스 크기 사용
            let naturalHeight = croppers[i].canvas.height;
            
            let canvas = croppers[i].getCroppedCanvas({
                width: naturalWidth, 
                height: naturalHeight
            });
            croppedCanvases.push(canvas);
        }

        cropperSwiper.removeAllSlides();

        const maxWidth = $('.cropperSwiper').width();
        const maxHeight = $('.cropperSwiper').height();

        for (let i = 0; i < croppedCanvases.length; i++) {
            let canvasId = `mosaicCanvas${i}`;
            const { width, height } = adjustCanvasSize(croppedCanvases[i], maxWidth, maxHeight);
            cropperSwiper.appendSlide(
                `<div class="swiper-slide" style="display: flex; justify-content: center; align-items: center;">
                    <canvas id="${canvasId}" width="${width}" height="${height}"></canvas>
                </div>`
            );
        }
        cropperSwiper.update();

        $('#cropperBoxTitle').text('사진편집(모자이크)');
        $('#finishCropperBtn').attr('onclick', 'mosaicDone()');
        $('#cropperEvent').addClass('hide');
        $('#mosaicEvent').removeClass('hide');

        await applyMosaic(croppedCanvases);
    }

    // 캔버스 크기를 Swiper에 맞게 조정하는 함수
    function adjustCanvasSize(canvas, maxWidth, maxHeight) {
        let width = canvas.width;
        let height = canvas.height;
        const aspectRatio = width / height;

        if (width > maxWidth) {
            width = maxWidth;
            height = width / aspectRatio;
        }
        if (height > maxHeight) {
            height = maxHeight;
            width = height * aspectRatio;
        }
        return { width: Math.floor(width), height: Math.floor(height) };
    }

    function rotateIfNeeded(canvas) {
        const width = canvas.width;
        const height = canvas.height;

        if (width > height) {
            // 🔁 회전 필요
            const rotatedCanvas = document.createElement('canvas');
            rotatedCanvas.width = height;
            rotatedCanvas.height = width;

            const ctx = rotatedCanvas.getContext('2d');
            ctx.translate(height, 0);
            ctx.rotate(90 * Math.PI / 180);
            ctx.drawImage(canvas, 0, 0);

            return rotatedCanvas;
        }

        return canvas; // 이미 세로면 그대로
    }


    // 모자이크 완료 후 업로드
    async function mosaicDone() {
        let formData = new FormData();
        let canvases = document.querySelectorAll('.swiper-slide canvas');

        for (let i = 0; i < canvases.length; i++) {
            let originalCanvas = document.createElement('canvas');
            originalCanvas.width = croppedCanvases[i].width;
            originalCanvas.height = croppedCanvases[i].height;
            let originalCtx = originalCanvas.getContext('2d');

            originalCtx.drawImage(croppedCanvases[i], 0, 0, originalCanvas.width, originalCanvas.height);
            await applyMosaicToOriginal(canvases[i], originalCanvas, croppedCanvases[i]);

            // let blob = await canvasToBlob(originalCanvas);
            let blob = await canvasToBlob(rotateIfNeeded(originalCanvas));
            formData.append('files[]', blob, files[i].name);
        }
        uploadCropperImg(formData);
    }

    // 축소된 캔버스의 모자이크를 원본 크기로 복원하는 함수
    async function applyMosaicToOriginal(smallCanvas, originalCanvas, originalSourceCanvas) {
        let smallCtx = smallCanvas.getContext('2d');
        let originalCtx = originalCanvas.getContext('2d');

        const smallWidth = smallCanvas.width;
        const smallHeight = smallCanvas.height;
        const originalWidth = originalCanvas.width;
        const originalHeight = originalCanvas.height;

        const scaleX = originalWidth / smallWidth;
        const scaleY = originalHeight / smallHeight;

        const mosaicSize = 10; // 축소된 캔버스에서의 모자이크 크기
        const originalMosaicSize = Math.ceil(mosaicSize * scaleX); // 빈틈 방지를 위해 올림

        let tempCanvas = document.createElement('canvas');
        tempCanvas.width = smallWidth;
        tempCanvas.height = smallHeight;
        let tempCtx = tempCanvas.getContext('2d');
        tempCtx.drawImage(originalSourceCanvas, 0, 0, smallWidth, smallHeight);

        let smallImageData = smallCtx.getImageData(0, 0, smallWidth, smallHeight);
        let smallData = smallImageData.data;
        let tempImageData = tempCtx.getImageData(0, 0, smallWidth, smallHeight);
        let tempData = tempImageData.data;

        // 모자이크가 적용된 영역 복원 (빈틈 없이)
        for (let y = 0; y < smallHeight; y += mosaicSize) {
            for (let x = 0; x < smallWidth; x += mosaicSize) {
                let isMosaic = false;
                let r = 0, g = 0, b = 0, count = 0;

                for (let dy = 0; dy < mosaicSize && y + dy < smallHeight; dy++) {
                    for (let dx = 0; dx < mosaicSize && x + dx < smallWidth; dx++) {
                        let smallIdx = ((y + dy) * smallWidth + (x + dx)) * 4;
                        let tempIdx = smallIdx;

                        let diffR = Math.abs(smallData[smallIdx] - tempData[tempIdx]);
                        let diffG = Math.abs(smallData[smallIdx + 1] - tempData[tempIdx + 1]);
                        let diffB = Math.abs(smallData[smallIdx + 2] - tempData[tempIdx + 2]);

                        if (diffR > 5 || diffG > 5 || diffB > 5) {
                            isMosaic = true;
                        }

                        r += smallData[smallIdx];
                        g += smallData[smallIdx + 1];
                        b += smallData[smallIdx + 2];
                        count++;
                    }
                }

                if (isMosaic && count > 0) {
                    r = Math.floor(r / count);
                    g = Math.floor(g / count);
                    b = Math.floor(b / count);

                    // 빈틈 없이 채우기 위해 크기를 조금 더 크게 설정
                    originalCtx.fillStyle = `rgb(${r}, ${g}, ${b})`;
                    originalCtx.fillRect(
                        Math.floor(x * scaleX),
                        Math.floor(y * scaleY),
                        originalMosaicSize + 1, // +1로 경계 채움
                        originalMosaicSize + 1  // +1로 경계 채움
                    );
                }
            }
        }
    }

    // 캔버스를 Blob으로 변환하는 헬퍼 함수
    function canvasToBlob(canvas) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => resolve(blob));
        });
    }

    // 서버 업로드 함수
    function uploadCropperImg(formData) {
        $('#percent').text(0);
        $('#progressLoader').removeClass('hide');
        $('html, body').css('overflow', 'hidden');

        $.ajax({
            type: 'post',
            url: '/userApi/photoUpload2',
            dataType: 'json',
            timeout: 60000,
            contentType: false,
            processData: false,
            data: formData,
            xhr: function () {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function (event) {
                    if (event.lengthComputable) {
                        let percent = (event.loaded / event.total) * 100;
                        $('#percent').text(parseInt(percent));
                    }
                });
                return xhr;
            },
            success: function (readPhotoImgRes) {
                if (!readPhotoImgRes.result) {
                    showAlert(readPhotoImgRes.msg);
                    return false;
                }
                setReadPhoto(readPhotoImgRes.files, $('#photoType').val());
                photos = photos.concat(readPhotoImgRes.files);
                closeCropper();
                setPriceInfo();
            },
            complete: function () {
                $('html, body').css('overflow', '');
                $('#progressLoader').addClass('hide');
            },
            error: function (request, status, error) {
                alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
            }
        });
    }

    let isMosaicMode = false;

    // 모자이크 활성화 토글
    function toggleMosaicMode() {
        isMosaicMode = !isMosaicMode;
        const icon = document.getElementById('mosaicToggleBtn');
        icon.style.color = isMosaicMode ? '#ff6666' : '';

        if (isMosaicMode) {
            enableMosaicDrawing();
        } else {
            disableMosaicDrawing();
        }
    }

    function enableMosaicDrawing() {
        const canvas = document.querySelectorAll('.swiper-slide canvas')[cropperSwiper.activeIndex];
        const ctx = canvas.getContext('2d');
        const mosaicSize = 10;
        let isDrawing = false;

        const startDrawing = (e) => {
            isDrawing = true;
            draw(e);
        };
        const draw = (e) => {
            if (!isDrawing) return;
            const [x, y] = getCoordinates(e, canvas);
            applyMosaicEffect(ctx, x, y, mosaicSize);
        };
        const stopDrawing = () => {
            isDrawing = false;
        };

        // 이벤트 바인딩 및 저장
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        mosaicListeners = [
            { canvas, type: 'mousedown', fn: startDrawing },
            { canvas, type: 'mousemove', fn: draw },
            { canvas, type: 'mouseup', fn: stopDrawing },
            { canvas, type: 'touchstart', fn: startDrawing },
            { canvas, type: 'touchmove', fn: draw },
            { canvas, type: 'touchend', fn: stopDrawing },
        ];
    }

    function disableMosaicDrawing() {
        mosaicListeners.forEach(({ canvas, type, fn }) => {
            canvas.removeEventListener(type, fn);
        });
        mosaicListeners = [];
    }

    function getCoordinates(e, canvas) {
        let rect = canvas.getBoundingClientRect();
        if (e.touches) {
            return [e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top];
        }
        return [e.clientX - rect.left, e.clientY - rect.top];
    }

    function applyMosaicEffect(ctx, x, y, mosaicSize) {
        let blockX = Math.floor(x / mosaicSize) * mosaicSize;
        let blockY = Math.floor(y / mosaicSize) * mosaicSize;

        if (blockX + mosaicSize > ctx.canvas.width || blockY + mosaicSize > ctx.canvas.height) return;

        let imageData = ctx.getImageData(blockX, blockY, mosaicSize, mosaicSize);
        let data = imageData.data;
        let r = 0, g = 0, b = 0, count = 0;

        for (let i = 0; i < data.length; i += 4) {
            r += data[i];
            g += data[i + 1];
            b += data[i + 2];
            count++;
        }

        r = Math.floor(r / count);
        g = Math.floor(g / count);
        b = Math.floor(b / count);

        ctx.fillStyle = `rgb(${r}, ${g}, ${b})`;
        ctx.fillRect(blockX, blockY, mosaicSize, mosaicSize);
    }




    // 모자이크 초기화
    function mosaicReset() {
        const activeIndex = cropperSwiper.activeIndex;
        let canvases = document.querySelectorAll('.swiper-slide canvas');
        let contexts = Array.from(canvases).map(canvas => canvas.getContext('2d'));

        contexts[activeIndex].clearRect(0, 0, canvases[activeIndex].width, canvases[activeIndex].height);
        contexts[activeIndex].drawImage(
            croppedCanvases[activeIndex],
            0,
            0,
            canvases[activeIndex].width,
            canvases[activeIndex].height
        );
    }

    let mosaicListeners = [];

    async function applyMosaic(croppedCanvases) {
        let canvases = document.querySelectorAll('.swiper-slide canvas');
        let contexts = Array.from(canvases).map(canvas => canvas.getContext('2d'));

        croppedCanvases.forEach((canvas, index) => {
            contexts[index].clearRect(0, 0, canvases[index].width, canvases[index].height);
            contexts[index].drawImage(canvas, 0, 0, canvases[index].width, canvases[index].height);
        });
    }


    // 모자이크 이벤트 설정 함수
    // async function applyMosaic(croppedCanvases) {
    //     let canvases = document.querySelectorAll('.swiper-slide canvas');
    //     let contexts = Array.from(canvases).map(canvas => canvas.getContext('2d'));

    //     croppedCanvases.forEach((canvas, index) => {
    //         contexts[index].clearRect(0, 0, canvases[index].width, canvases[index].height);
    //         contexts[index].drawImage(canvas, 0, 0, canvases[index].width, canvases[index].height);
    //     });

    //     const mosaicSize = 10;
    //     let isDrawing = false;
    //     let lastX, lastY;

    //     canvases.forEach((canvas, index) => {
    //         canvas.addEventListener('mousedown', startDrawing);
    //         canvas.addEventListener('mousemove', drawMosaic);
    //         canvas.addEventListener('mouseup', stopDrawing);
    //         canvas.addEventListener('touchstart', startDrawing);
    //         canvas.addEventListener('touchmove', drawMosaic);
    //         canvas.addEventListener('touchend', stopDrawing);

    //         function startDrawing(e) {
    //             isDrawing = true;
    //             [lastX, lastY] = getCoordinates(e, canvas);
    //         }

    //         function drawMosaic(e) {
    //             if (!isDrawing) return;
    //             let [x, y] = getCoordinates(e, canvas);
    //             applyMosaicEffect(contexts[index], x, y);
    //             [lastX, lastY] = [x, y];
    //         }

    //         function stopDrawing() {
    //             isDrawing = false;
    //         }
    //     });

    //     function getCoordinates(e, canvas) {
    //         let rect = canvas.getBoundingClientRect();
    //         if (e.touches) {
    //             return [e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top];
    //         }
    //         return [e.clientX - rect.left, e.clientY - rect.top];
    //     }

    //     function applyMosaicEffect(ctx, x, y) {
    //         let blockX = Math.floor(x / mosaicSize) * mosaicSize;
    //         let blockY = Math.floor(y / mosaicSize) * mosaicSize;

    //         if (blockX + mosaicSize > ctx.canvas.width || blockY + mosaicSize > ctx.canvas.height) return;

    //         let imageData = ctx.getImageData(blockX, blockY, mosaicSize, mosaicSize);
    //         let data = imageData.data;
    //         let r = 0, g = 0, b = 0, count = 0;

    //         for (let i = 0; i < data.length; i += 4) {
    //             r += data[i];
    //             g += data[i + 1];
    //             b += data[i + 2];
    //             count++;
    //         }

    //         r = Math.floor(r / count);
    //         g = Math.floor(g / count);
    //         b = Math.floor(b / count);

    //         ctx.fillStyle = `rgb(${r}, ${g}, ${b})`;
    //         ctx.fillRect(blockX, blockY, mosaicSize, mosaicSize);
    //     }
    // }
    
    /**
     **********************************************
                    STEP - 3(END)
     **********************************************
    **/
    
    
    /**
     **********************************************
                    STEP - 4(START)
     **********************************************
    **/
    
    /* 파일 선택 버튼을 클릭하여 파일 선택 창을 열기 */
    function addFile(){
        $('#fileController').click(); 
    }

    /* 이미지 파일 선택 버튼을 클릭하여 이미지 파일 선택 창을 열기 */
    function addImageFile() {
        $('#imgFileController').click();
    }
    
    /* 선택한 파일 읽기 */
    async function readFile(input) {
        let formData = new FormData(), // FormData 객체 생성 (파일 업로드를 위한 데이터 구성)
            inputId = input.id, // 입력된 파일 컨트롤러의 ID
            filesHtml = ''; // 업로드된 파일에 대한 HTML을 저장할 변수

        /* 드래그 앤 드랍시 */
        if(!isMobile()) {
            inputId = 'allController'; // 모바일이 아닐 경우 모든 컨트롤러로 설정
        }                

        if (!input.files.length) {
            return; // 파일이 선택되지 않으면 함수 종료
        } else if(isMobile() && input.files.length > 3) {
            input.value = ''; // 파일 선택 초기화
            showAlert('파일은 최대 3개씩 업로드 가능합니다'); // 최대 파일 개수 초과 경고
            return;
        }

        showLoadingBar(); // 파일 업로드 진행 중 로딩 표시

        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i], // 파일 객체
                extension = file.name.split('.').pop().toLowerCase(), // 파일 확장자 추출
                pdfType = ['pdf'], // PDF 파일 형식
                imgType = ['jpg', 'jpeg', 'png'], // 이미지 파일 형식
                fileName = '', // 업로드할 수 있는 파일 형식
                isFile = false; // 파일 형식 확인 변수

            switch(inputId) {
                case 'allController': 
                    fileName = 'pdf, jpg, jpeg, png'; // 모든 형식 허용
                    isFile = (pdfType.concat(imgType)).indexOf(extension) > -1; // 허용된 확장자에 포함되면 true
                    break;
                case 'fileController':                    
                    fileName = 'pdf'; // PDF만 허용
                    isFile = pdfType.indexOf(extension) > -1;
                    break;
                case 'imgFileController':
                    fileName = 'jpg, jpeg, png'; // 이미지만 허용
                    isFile = imgType.indexOf(extension) > -1;
                    break;
            }

            if (!isFile) {
                hideLoadingBar(); // 로딩 바 숨김
                showAlert(`(${fileName})의 파일형식만 업로드가능합니다.`); // 잘못된 파일 형식 경고
                return;
            }

            let pageCount = 1; // PDF 페이지 수 초기값 설정

            formData.append('files[]', file); // 파일을 FormData에 추가
            if(extension == 'pdf') {
                let arrayBuffer = await file.arrayBuffer(), // PDF 파일을 배열로 읽기
                    pdfData = new Uint8Array(arrayBuffer), // Uint8Array로 변환
                    pdf = await pdfjsLib.getDocument({ data: pdfData }).promise; // PDF 문서 로드
                pageCount = pdf.numPages; // PDF 페이지 수 저장
            }            
            formData.append('pageCount[]', pageCount); // 페이지 수를 FormData에 추가
        }

        const readFileRes = await postFormJson('/userFileApi/fileUpload', formData); // 서버로 파일 전송

        if (!readFileRes.result) {
            showAlert(readFileRes.msg); // 업로드 실패 시 오류 메시지 표시
            return false;
        }

        setReadFile(readFileRes.files); // 파일을 읽고 화면에 표시
        pdfFiles = pdfFiles.concat(readFileRes.files); // PDF 파일 목록에 추가

        input.value = ''; // 파일 선택 초기화
        hideLoadingBar(); // 로딩 바 숨김
    }
    
    /* 파일출력 */
    function setReadFile(files, color = '컬러', colors = '') {
        let filesHtml = ''; // 파일 목록을 저장할 변수 초기화

        for (let i = 0; i < files.length; i++) {
            let data = files[i]; // 파일 데이터 추출

            if (colors) { // colors 매개변수가 있으면 색상을 배열로 분리하여 적용
                color = colors.split("/")[i];
            }

            let clsColor = color == '컬러' ? 'color' : 'black'; // 선택된 색상이 '컬러'이면 'color', 아니면 'black' 클래스 적용

            // 파일 정보를 HTML로 생성
            filesHtml += `
            <div class="fileWrap">
                <span class="fileName">${data.originalFileName}</span>
                <span class="fileCnt">${data.pageCount}장</span>
                <div class="btnColorBox">
                    <button class="btnColor black ${clsColor == 'black' ? 'active' : ''}" onclick="changeFileColor($(this))">블랙</button>
                    <button class="btnColor color ${clsColor == 'color' ? 'active' : ''}" onclick="changeFileColor($(this))">컬러</button>
                </div>
                <span class="delBtn" onclick="deleteFile($(this))"><i class="fas fa-trash-alt"></i></span>
            </div>`;
        }

        $('#fileList').append(filesHtml); // 파일 목록을 화면에 추가
        setPriceInfo(); // 가격 정보 업데이트
    }
    
    // 모든 파일 색상을 변경하는 함수
    function changeAllFileColor(color) {
        let $btnColor = $(`.btnColor.${color}`); // 지정된 색상의 모든 버튼을 찾음

        // 각 버튼에 대해 색상 변경
        for (let i = 0; i < $btnColor.length; i++) {
            let $target = $btnColor.eq(i);            
            changeFileColor($target); // 색상 변경 함수 호출
        }
    }

    // 개별 파일 색상을 변경하는 함수
    function changeFileColor($this) {
        let $target = $this.parent('.btnColorBox'); // 버튼의 부모 요소를 찾음

        // 기존의 'active' 클래스를 제거하고, 선택된 버튼에 'active' 클래스 추가
        $target.find('.btnColor').removeClass('active');        
        $this.addClass('active');                
        setPriceInfo(); // 가격 정보 업데이트
    }

    // 파일을 삭제하는 함수
    function deleteFile($this) {
        let $target = $this.closest('.fileWrap'), // 삭제할 파일 요소 찾기
            index = $target.index(); // 해당 파일의 인덱스 추출

        $target.remove(); // 파일 삭제
        pdfFiles.splice(index, 1); // pdfFiles 배열에서 해당 파일 삭제                
        setPriceInfo(); // 가격 정보 업데이트
    }

    
    function previewLibraryImage(path) {
        $('#libraryPreviewImg').attr('src', path);
        $('#libraryPreviewModal').fadeIn();
    }

    $('#libraryPreviewModal').on('click', function() {
        $(this).fadeOut();
    });

    /* 자료 선택 */
    function choicelibrary($this) {
        let index = $this.index(),
            libraryIdx = $this.data('idx'),
            cateNameRaw = $this.data('cate-name'),
            cateName = (typeof cateNameRaw === 'string') ? cateNameRaw : String(cateNameRaw || ''),
            isBroadcast = cateName.includes('방송표'),
            priceText = $this.find('.libraryPrice').text().replace('원', '').replace(/,/g, ''),
            filePath = $this.data('file-path'),
            libraryData = {
                idx: libraryIdx,
                name: $this.find('.libraryName').text(),
                price: parseInt(priceText) || 0,
                pageCount: $this.data('page-count'),
				libCount: 1,
                color: 'color',
                cateName: cateName,
                isBroadcast: isBroadcast,
                filePath: filePath,
                originalFileName: $this.find('.libraryName').text() + '.pdf'
            };

        const existingIndex = libraryIndexes.indexOf(index);
		const library = selectedLibraries.find(lib => lib.idx === libraryIdx);

        if (library) {
			library.libCount += 1;
			$this.closest('.fileWrap.libraryFile').find('.number').text(library.libCount);
			setPriceInfo();
			$this.addClass('active');
        } else {
            libraryIndexes.push(index);
            selectedLibraries.push(libraryData);
            $this.addClass('active');
        }

        updateSelectedLibraries();
        setPriceInfo();
    }


    /* 선택한 자료 목록 업데이트 함수 */
    function updateSelectedLibraries() {
        let html = '';
        
        selectedLibraries.forEach((lib, idx) => {
            let priceText = `${comma(lib.pageCount * (lib.color === 'black' ? 150 : 300))}원`;
            
            html += `
            <div class="fileWrap libraryFile" data-lib-idx="${lib.idx}" data-cate-name="${lib.cateName}" data-file-path="${lib.filePath}">
                <span class="fileName">${lib.name}</span>
                <span class="spinner">
					<button class="arrow up"></button>
					<span class="number">${lib.libCount}</span>
					<button class="arrow down"></button>
				</span>
                <div class="btnColorBox">
                    <button class="btnColor black ${lib.color === 'black' ? 'active' : ''}" 
                            onclick="changeLibraryColor($(this).closest('.fileWrap.libraryFile'), 'black', ${idx})">블랙</button>
                    <button class="btnColor color ${lib.color === 'color' ? 'active' : ''}" 
                            onclick="changeLibraryColor($(this).closest('.fileWrap.libraryFile'), 'color', ${idx})">컬러</button>
                </div>
                <span class="delBtn" onclick="removeLibrary($(this).closest('.fileWrap.libraryFile'), ${idx})">
                    <i class="fas fa-trash-alt"></i>
                </span>
            </div>`;
        });
        
        $('#libraryList').html(html);
    }

	async function setLibraryFiles(libraryFiles) {
		const res = await postJson('/userApi/getLibrary', {
			cateIdx: 0
		});

		const list = res.list || [];
		if (list.length <= 0) {
			$('#libraryList').html('<p class="empty">등록된 자료가 없습니다.</p>');
			return;
		}

		for (const libraryFile of libraryFiles) {
			let index = list.findIndex(item => item.idx === libraryFile.libraryIdx);
			let filteredData = index !== -1 ? list[index] : null;
			if (filteredData == null) {
				continue;
			}
			let libraryData = {
				idx: filteredData.idx,
				name: filteredData.name,
				price: parseInt(filteredData.price) || 0,
				pageCount: filteredData.pageCount,
				color: 'color',
				cateName: filteredData.cateName,
				isBroadcast: filteredData.cateName.includes('방송표'),
				filePath: filteredData.filePath,
				originalFileName: filteredData.originalFileName,
				libCount: parseInt(libraryFile.libCount) || 1,
			};

			libraryIndexes.push(index);
			selectedLibraries.push(libraryData);
		}

		updateSelectedLibraries();
		setPriceInfo();

		let listHtml = '';
		const sevenDaysAgo = new Date();
		sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
		sevenDaysAgo.setHours(0, 0, 0, 0);
		list.forEach((data, index) => {
			const regDate = new Date(data.regDate.replace(' ', 'T'));
			const isNew = regDate >= sevenDaysAgo;
			const isBroadcast = (data.cateName || '').includes('방송표'); // 카테고리 이름으로 방송표 확인

			listHtml += `
                    <div class="libraryViewBox ${libraryIndexes.includes(index) ? 'active' : ''}"
                        onclick="choicelibrary($(this))"
                        data-cate="${data.categoryIdx}"
                        data-idx="${data.idx}"
                        data-page-count="${data.pageCount}"
                        data-cate-name="${data.cateName}"
                        data-file-path="${data.filePath}">
                        ${isNew ? `<div class="newBadge"><img src="/assets/image/new.png" alt="New"></div>` : ''}
                        <div class="dirText" onclick="event.stopPropagation(); previewLibraryImage('${data.thumbUrl}')">미리보기</div>
                        <div class="libraryImgBox">
                            <img src="${data.thumbUrl}"/>
                        </div>
                        <p class="libraryName">${data.name}</p>
                        <p class="libraryPrice">${comma(data.price)+'원'}</p>
                    </div>
                `;
		});

		$('#libraryWrap').html(listHtml);
	}

    /* 자료 색상 변경 함수 */
    function changeLibraryColor($wrap, color, index) {
        // 선택한 자료의 색상 업데이트
        selectedLibraries[index].color = color;
        
        // UI 업데이트
        $wrap.find('.btnColor').removeClass('active');
        $wrap.find(`.btnColor.${color}`).addClass('active');
        
        setPriceInfo();
    }

    /* 자료 삭제 함수 */
    function removeLibrary($wrap, index) {
        const libIdx = $wrap.data('lib-idx');
        
        // 선택 목록에서 제거
        libraryIndexes.splice(index, 1);
        selectedLibraries.splice(index, 1);
        
        // 라이브러리 뷰에서 active 클래스 제거
        $(`.libraryViewBox[data-idx="${libIdx}"]`).removeClass('active');
        
        updateSelectedLibraries();
        setPriceInfo();
    }


    async function changeLibraryCate(choiceCateIdx) {
        const cateIdx = parseInt(choiceCateIdx); // 선택된 카테고리
        
        let listHtml = '';

        // 모든 자료실 카테고리 버튼에서 active 제거
        $('.cate2Box .cate2').removeClass('active');

        // 선택된 카테고리에 active 추가
        $(`.cate2[data-cate="${cateIdx}"]`).addClass('active');

        // 현재 선택된 카테고리가 방송표인지 확인
        const isBroadcastCategory = $(`.cate2[data-cate="${cateIdx}"]`).text().includes('방송표');

        // if (selectedLibraries.length > 0) {
        //     const remainingLibs = selectedLibraries.filter(lib => {
        //         const $libBox = $(`.libraryViewBox[data-idx="${lib.idx}"]`);
        //         // 현재 카테고리에 속하거나 방송표 자료인 경우 유지
        //         return ($libBox.length > 0 && $libBox.data('cate') == choiceCateIdx) ||
        //             (isBroadcastCategory && lib.isBroadcast);
        //     });
		//
        //     // 다른 카테고리에 속하는 자료는 제거
        //     if (remainingLibs.length !== selectedLibraries.length) {
        //         selectedLibraries = remainingLibs;
        //         libraryIndexes = remainingLibs.map(lib =>
        //             $(`.libraryViewBox[data-idx="${lib.idx}"]`).index()
        //         );
        //         updateSelectedLibraries();
        //         setPriceInfo();
        //     }
        // }

        // 서버에 해당 카테고리 자료 요청
        const res = await postJson('/userApi/getLibrary', {
            cateIdx: cateIdx
        });

        const list = res.list || [];

        // 7일 이내 자료 표시용 기준일 계산
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        sevenDaysAgo.setHours(0, 0, 0, 0);

        if (!list.length) {
            listHtml = '<p class="empty">등록된 자료가 없습니다.</p>';
        } else {
            for (let data of list) {
                const regDate = new Date(data.regDate.replace(' ', 'T'));
                const isNew = regDate >= sevenDaysAgo;
                const isBroadcast = (data.cateName || '').includes('방송표'); // 카테고리 이름으로 방송표 확인
                listHtml += `
                    <div class="libraryViewBox" 
                        onclick="choicelibrary($(this))" 
                        data-cate="${data.categoryIdx}" 
                        data-idx="${data.idx}" 
                        data-page-count="${data.pageCount}"
                        data-cate-name="${data.cateName}"
                        data-file-path="${data.filePath}">
                        ${isNew ? `<div class="newBadge"><img src="/assets/image/new.png" alt="New"></div>` : ''}
                        <div class="dirText" onclick="event.stopPropagation(); previewLibraryImage('${data.thumbUrl}')">미리보기</div>
                        <div class="libraryImgBox">
                            <img src="${data.thumbUrl}"/>
                        </div>
                        <p class="libraryName">${data.name}</p>
                        <p class="libraryPrice">${comma(data.price)+'원'}</p>
                    </div>
                `;
            }
        }

        $('#libraryWrap').html(listHtml);
    }

    // 페이지 로드 후, 기본 카테고리(전체) 로딩
    $(document).ready(function () {
        changeLibraryCate(<?= $libraryCateIdx ?>);
    });

    
    /**
     **********************************************
                    STEP - 4(END)
     **********************************************
    **/
    
    /**
     **********************************************
                    STEP - 5(START)
     **********************************************
    **/
    
    /**
     * 주소지 선택 팝업을 열거나 닫는 함수
     * @param {string} type - 'show'일 경우 팝업을 열고, 'close'일 경우 팝업을 닫음
     */
    async function onPostPopup(type) {
        let $postPopup = $('#postPopup'); // 팝업 요소 선택

        if (type == 'show') { // 팝업 열기
            let list = '',
                $senderName = $('#senderName'),  // 보내는 사람 이름 입력 필드
                $senderTel = $('#senderTel'),    // 보내는 사람 전화번호 입력 필드
                $mbPassword = $('#mbPassword');  // 4자리 숫자 비밀번호 입력 필드

            // 비로그인 사용자의 경우, 필수 입력 값 체크
            if (isLogin == 'N') {
                if (!$senderName.val() || !$senderTel.val() || $mbPassword.val().length != 4) {
                    showAlert("이름, 전화번호, 임의의 숫자 4자리를 정확히 입력해주세요.");
                    return false;
                }
            }

            // 서버에서 저장된 주소 목록을 가져오기 위한 API 호출
            const getPostListRes = await postJson('/addressApi/getPostList', {
                memberIdx: memberIdx,             // 회원 고유 번호
                senderName: $senderName.val(),    // 입력한 보내는 사람 이름
                senderTel: $senderTel.val(),      // 입력한 보내는 사람 전화번호
                mbPassword: $mbPassword.val()     // 입력한 4자리 비밀번호
            });

            // 주소지가 없는 경우 알림 표시 후 종료
            if (!getPostListRes.list.length) {
                showAlert("저장되어 있는 주소지가 존재하지 않습니다.");
                return false;
            }

            // 가져온 주소 목록을 반복문을 통해 리스트로 생성
            for (let i = 0; i < getPostListRes.list.length; i++) {
                let data = getPostListRes.list[i];

                list += `<div class="postBox">
                            <p class="postHead"><i class="fas fa-truck" style="margin-right: 5px;"></i> 보내는분</p>
                            <p class="postContent">주소 - ${data['senderAddr']}</p>
                            <p class="postContent">상세주소 - ${data['senderAddrDetail']}</p>
                            <p class="postContent">성함 - ${data['senderName']}</p>
                            <p class="postContent">휴대번호 - ${data['senderTel']}</p>

                            <p class="postHead"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> 받는분</p>
                            <p class="postContent">주소 - ${data['receiverAddr']}</p>
                            <p class="postContent">상세주소 - ${data['receiverAddrDetail']}</p>
                            <p class="postContent">성함 - ${data['receiverName']}</p>
                            <p class="postContent">휴대번호 - ${data['receiverTel']}</p>
                            <button class="postChoiceBtn" onclick="choicePost(${i})">선택</button>
                        </div>`;
            }

            // 생성된 주소 목록을 화면에 표시
            $('#postList').html(list);
            postList = getPostListRes.list; // 전역 변수에 주소 목록 저장

            // 주소 개수 표시
            $('#totalPostCnt').text(`(${postList.length}개)`);

            // 팝업 창 열기
            $postPopup.addClass('show');

        } else { // 팝업 닫기
            $postPopup.removeClass('show');
        }
    }

    
    /**
     * 선택한 주소 정보를 입력 필드에 반영하는 함수
     * @param {number} index - 사용자가 선택한 주소 목록의 인덱스
     */
    function choicePost(index) {        
        // 선택한 보내는 사람 주소 정보를 입력 필드에 설정
        $("#senderAddr").val(postList[index]['senderAddr']); // 보내는 사람 주소
        $("#senderAddrDetail").val(postList[index]['senderAddrDetail']).attr('disabled', false); // 상세주소 (비활성화 해제)
        $("#senderName").val(postList[index]['senderName']); // 보내는 사람 이름
        $("#senderTel").val(postList[index]['senderTel']); // 보내는 사람 전화번호

        // 선택한 받는 사람 주소 정보를 입력 필드에 설정
        $("#receiverAddr").val(postList[index]['receiverAddr']); // 받는 사람 주소
        $("#receiverAddrDetail").val(postList[index]['receiverAddrDetail']).attr('disabled', false); // 상세주소 (비활성화 해제)
        $("#receiverName").val(postList[index]['receiverName']); // 받는 사람 이름
        $("#receiverTel").val(postList[index]['receiverTel']); // 받는 사람 전화번호

        // 주소 선택 팝업 닫기
        onPostPopup('close');
    }

    
    /**
     * 주소 선택 모달을 열거나 닫는 함수
     * @param {string} type - 'show'일 경우 모달을 열고, 그 외 값이면 모달을 닫음
     * @param {string} addrType - 선택한 주소 유형 ('army' 또는 'prison' 등)
     */
    function openAddrModal(type, addrType = '') {                        
        let $addrModal = $('#addrModal'); // 주소 선택 모달 엘리먼트 가져오기

        if (type == 'show') {  // 모달 열기
            let postSelectHtml = "<option value=''>선택</option>"; // 기본 선택 옵션 추가
            let adrType = addressType[addrType]; // 선택한 주소 유형에 해당하는 리스트 가져오기

            // 주소 유형 목록을 <option> 태그로 변환하여 추가
            for (let adr of adrType) {                
                postSelectHtml += `<option value="${adr}">${adr}</option>`;
            }

            // 모달 제목 설정 ('군대 훈련소' 또는 '구치소/교도소/소년원')
            $('#addrTitle').html(addrType == 'army' ? '군대 훈련소' : '구치소/교도소/소년원');

            // 주소 선택 드롭다운 리스트 업데이트 및 변경 시 이벤트 핸들러 추가
            $('#postSelect').html(postSelectHtml).attr('onchange', `changeAddrSelect('${addrType}')`);

            // 기본적으로 안내 문구 표시
            $("#addrList").html('<p class="empty" style="text-align:center">검색하실 항목을 선택해주세요.</p>');

            // 모달을 표시
            $addrModal.addClass('show');
        } else {  // 모달 닫기
            $addrModal.removeClass('show');
        }
    }


    // 유저 기본 주소 정보를 전역 변수로 저장
    const defaultZip = '<?= $this->user['zipCode'] ?? '' ?>';
    const defaultAddr = '<?= $this->user['senderAddr'] ?? '' ?>';
    const defaultAddrDetail = '<?= $this->user['senderAddrDetail'] ?? '' ?>';
    const defaultName = '<?= $this->user['senderName'] ?? '' ?>';

    let isPoboxActive = false;

    function togglePobox() {
        let btn = $('#poboxToggleBtn');

        if (!isPoboxActive) {
            // 사서함 서비스 켜기
            $('#senderZipCode').val('49477');

            $('#senderAddr')
                .val('(49477)부산 사하구 하신중앙로27번길 6')
                .prop('readonly', true)
                .addClass('no-click')
                .off('click');

            $('#senderAddrDetail')
                .val('3층' + ' (<?= $this->user['idx'] ?>)')
                .prop('readonly', true)
                .addClass('no-click')
                .off('click');

			if (defaultName) {
				$('#senderName')
					.val(defaultName)
					.prop('readonly', false)
				// .addClass('no-click')
				// .off('click');
			} else {
				$('#senderName')
					.val('')
					.prop('readonly', false)
				// .addClass('no-click')
				// .off('click');
			}

            btn.text('사서함 서비스 사용중').addClass('active');
            isPoboxActive = true;
        } else {
            // 사서함 서비스 끄기 - 원래 주소 정보로 복원
            $('#senderZipCode').val(defaultZip);

            $('#senderAddr')
                .val(defaultAddr)
                .prop('readonly', false)
                .removeClass('no-click')
                .on('click', function() {
                    openDaumPostcode($('#senderZipCode'), $('#senderAddr'), $('#senderAddrDetail'), 1);
                });

            $('#senderAddrDetail')
                .val(defaultAddrDetail)
                .prop('readonly', false)
                .removeClass('no-click');

            $('#senderName')
                .val(defaultName)
                .prop('readonly', false)
                .removeClass('no-click');

            btn.text('사서함 서비스').removeClass('active');
            isPoboxActive = false;
        }
    }






    
    /**
     * 선택한 지역에 따라 주소 목록을 업데이트하는 함수
     * @param {string} adrType - 선택한 주소 유형 ('army' 또는 'prison' 등)
     */
    function changeAddrSelect(adrType) {
        let list = ''; // 주소 목록 HTML을 저장할 변수
        let adrList = addressList[adrType]; // 선택한 유형의 주소 리스트 가져오기
        let choiceType = $('#postSelect option:selected').val(); // 사용자가 선택한 지역 값 가져오기                

        // 주소 리스트를 순회하며 선택한 지역과 일치하는 주소만 필터링
        for (let i = 0; i < adrList.length; i++) {
            let data = adrList[i];
            let addressDetail = !data.addressDetail ? data.name : data.addressDetail; // 상세 주소 설정 (없으면 name 사용)

            if (data.region != choiceType) continue; // 선택한 지역과 일치하지 않으면 건너뛰기            

            // 주소 정보를 HTML 요소로 변환하여 리스트에 추가
            list += `<div class="postBox">
                        <p class="postHead"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> ${data.name}</p>
                        <p class="postContent">주소 - (${data.post})${data.address}</p>
                        <p class="postContent">상세주소 - ${addressDetail}</p>
                        <button class="postChoiceBtn" onclick="choiceAddr('${data.post}', '${data.address}', '${addressDetail}')">선택</button>
                    </div>`;
        }

        // 생성된 주소 리스트를 HTML에 반영
        $('#addrList').html(list);
    }

    /**
     * 선택한 주소 정보를 입력 필드에 반영하는 함수
     * @param {string} post - 우편번호
     * @param {string} address - 기본 주소
     * @param {string} addressDetail - 상세 주소
     */
    function choiceAddr(post, address, addressDetail) {        
        // 받는 사람 주소 필드에 선택한 주소 설정
        $("#receiverAddr").val(`(${post})${address}`);
        $("#receiverAddrDetail").val(addressDetail).attr('disabled', false); // 상세주소 입력 가능하도록 활성화

        // 주소 선택 모달 닫기
        openAddrModal('hide');
    }
    
    /* 주소지 등록, 기본주소지 제어 함수 */
    function toggleisDefaultBox() {
        if($('#isSaveAddress').is(':checked')) {
            $('#isDefaultAddrBox').removeClass('hide');  // 주소 저장 체크되면 기본 주소 설정 박스 표시
            $('#isDefaultAddress').prop('checked', true);  // 기본 주소 설정 체크
        } else {
            $('#isDefaultAddrBox').addClass('hide');  // 주소 저장 체크 해제되면 기본 주소 설정 박스 숨김
            $('#isDefaultAddress').prop('checked', false);  // 기본 주소 설정 체크 해제
        }
    }
    
    /**
     **********************************************
                    STEP - 5(END)
     **********************************************
    **/
    
    /**
     **********************************************
                    STEP - 6(START)
     **********************************************
    **/
        
   /**
     * 스탬프 선택 상태를 활성화하는 함수
     * @param {jQuery} $this - 클릭된 스탬프 요소
     */
    function activeStamp($this) {
        // 모든 스탬프에서 'active' 클래스를 제거
        $('.stampBox').removeClass('active');

        // 클릭된 스탬프에 'active' 클래스를 추가
        $this.addClass('active');

        // 가격 정보를 업데이트
        setPriceInfo();
    }

    /**
     * 포인트를 전액 사용하도록 설정하는 함수
     * @param {string} isClick - 클릭 여부 ('Y'일 경우 클릭된 상태로 처리)
     */
    function useAllPoint(isClick) {
        let $allPayBtn = $('#allPayBtn'),            
            myPoint = parseInt($('#myPoint').val()),  // 사용자 보유 포인트
            totalPrice = parseInt($('#realTotalPrice').val()),  // 총 결제 금액
            payPoint = parseInt(uncomma($('#payPoint').val()));  // 사용된 포인트

        // 클릭 시 'active' 클래스를 토글 (전액 결제 버튼 활성화/비활성화)
        if (isClick == 'Y') {
            $allPayBtn.toggleClass('active');   
        }

        // 'active' 클래스가 있으면 보유 포인트가 총 금액보다 크면 총 금액만큼 포인트 설정
        if ($allPayBtn.hasClass('active')) {
            if (myPoint > totalPrice) {
                $('#payPoint').val(comma(totalPrice));  // 포인트가 총 금액보다 크면 총 금액만큼 설정
            } else {
                $('#payPoint').val(comma(myPoint));  // 그렇지 않으면 보유 포인트만큼 설정
            }
        } else if (isClick == 'Y') {
            // 버튼이 비활성화되면 포인트를 0으로 설정
            $('#payPoint').val(0);
        }

        // 결제 포인트가 총 금액을 초과하면 포인트를 총 금액으로 맞춤
        if (totalPrice - payPoint < 0 && !autoCallSave) {
            $('#payPoint').val(comma(totalPrice));
        }

        // 클릭 시 가격 정보를 업데이트
        if (isClick == 'Y') {
            setPriceInfo();
        }
    }

    /**
     * 포인트를 삭제하고 기본 상태로 되돌리는 함수
     */
    function deletePoint() {
        // 포인트 입력을 0으로 설정
        $('#payPoint').val(0);

        // 'allPayBtn' 버튼에서 'active' 클래스를 제거
        $('#allPayBtn').removeClass('active');

        // 가격 정보를 업데이트
        setPriceInfo();
    }
    
    /**
     * 예약 날짜 입력을 표시 또는 숨기는 함수
     * @param {string} isCheck - 'Y'일 경우 예약 날짜 입력란을 표시하고, 그 외의 경우 숨깁니다.
     */
    function checkReserv(isCheck) {
        let $dateBox = $('#dateBox');  // 예약 날짜 입력란 요소

        // isCheck가 'Y'이면 예약 날짜 입력란을 표시
        if (isCheck == 'Y') {
            $dateBox.removeClass('hide');
        } else {
            // isCheck가 'Y'가 아니면 예약 날짜 입력란을 숨김
            $dateBox.addClass('hide');
        }
    }

    /**
     * 결제창 최초 요청 시 실행되는 함수
     * 결제 유형과 우편 선택 여부, 가격 등을 확인하고, 결제 진행 여부를 묻는 메시지를 표시합니다.
    */
    function nicepayStart(){
        let $payType = $('.payTypeBtn.active'),  // 활성화된 결제 타입 버튼
            realTotalPrice = parseInt($('#realTotalPrice').val()),  // 실제 결제 금액
            payPoint = parseInt(uncomma($('#payPoint').val()));  // 사용 포인트

        // 우편을 선택하지 않으면 경고 메시지 표시
        if (!$('.stampBox.active').length) {
            showAlert('우편을 선택해주세요.');
            return;
        }

        let payType = $payType.data('type'),  // 선택된 결제 타입
            confirmMsg = '',  // 결제 진행 여부 확인 메시지
            isSamePrice = false;  // 동일한 결제 금액인지 확인

        // // 저장된 정보가 있고, 포인트 결제인 경우 포인트와 결제 금액 비교
        // if (saveIdx > 0 && info.payType == 'point' && info.payPoint != payPoint && realTotalPrice - payPoint != 0) {
        //     if (!$payType.length) {
        //         showAlert('결제타입을 선택해주세요.');
        //         return;
        //     }

        //     confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;
        // } else {
        //     if (payType == undefined) {
        //         payType = 'point';  // 결제 타입이 선택되지 않은 경우 기본값 'point'
        //     }            

        //     // 저장된 결제 정보와 현재 결제 정보가 동일한지 비교
        //     isSamePrice = saveIdx > 0 && (info.payType == payType && info.payPoint == payPoint && info.realTotalPrice == (realTotalPrice - payPoint));

        //     if (isSamePrice) {
        //         confirmMsg = '수정을 완료하시겠어요?';  // 동일한 금액인 경우 수정 여부 확인
        //     } else if (realTotalPrice - payPoint == 0) {
        //         // 포인트 결제만 남았을 때
        //         payType = 'point';
        //         confirmMsg = `포인트로 ${saveIdx > 0 ? '재' : ''}결제하시겠어요?`;
        //     } else {
        //         if (!$payType.length) {
        //             showAlert('결제타입을 선택해주세요.');
        //             return;
        //         }

        //         confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;  // 결제 타입을 선택했을 때 해당 결제 방식으로 진행할지 확인
        //     }
        // }

        console.log('realTotalPrice: ', realTotalPrice);
        console.log('payPoint: ', payPoint);
        // 💥 포인트 전액 결제
        if (realTotalPrice - payPoint == 0) {
            payType = 'point';
            confirmMsg = `포인트로 ${saveIdx > 0 ? '재' : ''}결제하시겠어요?`;
        }
        else if (saveIdx > 0 && info.payType == 'point' && info.payPoint != payPoint) {
            if (!$payType.length) {
                showAlert('결제타입을 선택해주세요.');
                return;
            }

            confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;
        }
        else {
            if (!$payType.length) {
                showAlert('결제타입을 선택해주세요.');
                return;
            }

            payType = $payType.data('type');

            isSamePrice = saveIdx > 0 && (info.payType == payType && info.payPoint == payPoint && info.realTotalPrice == (realTotalPrice - payPoint));

            if (isSamePrice) {
                confirmMsg = '수정을 완료하시겠어요?';
            } else {
                confirmMsg = `${$payType.text()}(으)로 진행하시겠어요?`;
            }
        }

		if (payType !== 'point' && (realTotalPrice - payPoint === 0)) {
			showAlert('결제 오류가 발생하였습니다. 고객센터로 문의해주시기 바랍니다.');
			return;
		}


        
        showConfirm(confirmMsg)
        .then(async (result) => {
            // 사용자가 '확인'을 클릭하지 않으면 종료
            if (!result.value) return;

            // 결제 정보 저장 시도
            let writeRes = await setWrite(payType);

            // 결제 정보 저장 실패 시, 경고 메시지 표시
            if (!writeRes.result) {
                showAlert(writeRes.msg);
                return;
            }

            // 저장된 글 인덱스 업데이트
			if (saveIdx == 0) {
				tmpSaveIdx = writeRes.writeIdx;
			}

            // 기존 정보가 있고, 결제 방식이 동일하거나 포인트 결제인 경우 수정된 내용 확인 후 처리
            if (saveIdx > 0 && (isSamePrice || payType == 'point' || payType == 'deposit')) {
                showAlert("수정되었습니다.")
                    .then(() => {
                        // 로그인 상태에 따라 적절한 마이페이지로 리다이렉트
                        if (isLogin == 'Y') {
                            nav.locationReplace('/mypage/myLetter', 'clear/b4');
                        } else {
                            nav.locationReplace(`/mypage/myLetter?mbName=${writeRes.mbName}&mbId=${writeRes.mbPhoneNumber}&mbPassword=${writeRes.mbPassword}`, 'clear/b4');
                        }
                    });
            }
            // 결제되지 않은 주문이고, 결제 방식이 'deposit'이 아닌 경우
            else if (writeRes.isPay == 'N' && payType != 'deposit') {
                let orderId = 'dongl-' + writeRes.writeIdx + '-' + generateUniqueRandomString(15);

                // React Native 환경에서 결제 페이지로 이동
                if (window.ReactNativeWebView) {
                    javascript: nav.locationHref(
                        `/letter/pay?writeIdx=${writeRes.writeIdx}&payType=${payType}`,
                        'z1'
                    );
                } else {
                    // 결제 요청을 위한 NICE PAY API 호출
                    AUTHNICE.requestPay({
                        clientId: 'R2_b675488f4ff44cba95de01808d9054ad', /* 'S2_af4543a0be4d49a98122e01ec2059a56' */
                        appScheme: `dongldn://`,
                        method: payType,
                        orderId: orderId,
                        amount: parseInt(writeRes.realTotalPrice),  // 결제 금액
                        goodsName: $('#goodsName').val(),
                        returnUrl: 'https://dongl.co.kr/payReturnUrl', // 결제 후 리턴될 URL
                        fnError: function (result) {
                            showAlert('결제가 취소되었습니다.');
                        }
                    });
                }
            }
            // 결제가 완료된 경우, 또는 입금결제인 경우 주문 완료 메시지 표시
            else {
                showAlert(payType == 'deposit' ? "주문조회 페이지에서 계좌이체를 진행해주세요." : "결제가 완료되었습니다.")
                    .then(() => {
                        // 로그인 상태에 따라 적절한 마이페이지로 리다이렉트
                        if (isLogin == 'Y') {
                            nav.locationReplace('/mypage/myLetter', 'clear/b4');
                        } else {
                            nav.locationReplace(`/mypage/myLetter?mbName=${writeRes.mbName}&mbId=${writeRes.mbPhoneNumber}&mbPassword=${writeRes.mbPassword}`, 'clear/b4');
                        }
                    });
            }
        });
    }
    
    /* 결제 방식 클릭함수 */
    function setPayBtn($this) {
        $('.payTypeBtn').removeClass('active');  // 모든 결제 방식 버튼에서 active 클래스를 제거
        $this.addClass('active');  // 클릭한 버튼에 active 클래스를 추가

        // 결제 방식이 'deposit'일 경우, 관련 정보를 보여줌
        if($this.data('type') == 'deposit') {            
            $('#depositInfoBox, #depositBox').removeClass('hide');  // 'deposit'일 경우 정보 박스 표시
        } else {            
            $('#depositInfoBox, #depositBox').addClass('hide');  // 그 외의 경우 숨김
        }
    }
    
    /* 현금 영수증 체크 함수 */
    function setIsCashReceipt() {
        $('#cashReceiptDetail').toggleClass('hide');  // 현금 영수증 정보 박스 토글
    }

    /* 현금영수증 발급 type에 따른 option 리스트 변경함수 */
    function changeDepositType(type) {
        let depositOptions = '';

        // depositTypes 객체의 키와 값을 순회하여 필터링 후, 옵션 생성
        Object.entries(depositTypes).forEach(([key, value]) => {
            // 'earnings' 유형일 경우 'business' 제외, 'expenditure'일 경우 'phone' 제외
            if((type == 'earnings' && key == 'business') || (type == 'expenditure' && key == 'phone')) return;

            depositOptions += `
                <option value="${key}">${value}</option>
            `;
        });

        // 필터링된 옵션들을 depositType select 요소에 추가
        $('#depositType').html(depositOptions);        
    }

   function renderStamps() {
	   const updatedStamps = [
		   { name: '일반우편', price: 1000, ment: '발송후 3~5일(평일기준)' },
		   { name: '준 등기우편(등기추적 가능)', price: 2500, ment: '발송후 3~5일(평일기준)' },
		   { name: '등기우편(일반등기)', price: 3200, ment: '발송후 3~5일(평일기준)' },
		   { name: '익일특급(빠른등기)', price: 4300, ment: '발송후 1~2일(평일기준)' }
	   ];

	   stamps = updatedStamps;

	   let html = '';
	   updatedStamps.forEach(function(item, index) {
		   html += `
                <div class="stampBox" data-stamp="${index}" onclick="activeStamp($(this));">
                    <p class="stampTitle">${item.name} <span class="price">${item.price.toLocaleString()}원</span></p>
                    <p class="ment">${item.ment}</p>
                </div>
            `;
	   });
	   $('.payBox').html(html);
   }
    
    /**
     **********************************************
                    STEP - 6(END)
     **********************************************
    **/    
    
    $(function() {
        
        // 이모지 버튼 클릭 이벤트
        $('#emojiBtn').click(function(e) {
            e.preventDefault();  // 기본 이벤트 동작 방지 (링크 클릭 시 이동 방지 등)

            // 선택된 index에 해당하는 .letterContent 요소에서 emojiPicker를 토글
            $('.letterContent').eq(selectIndex).emojiPicker('toggle');
        });

        // 한글 입력 시작을 감지하는 이벤트 핸들러
        $('#letter').on('compositionstart', '.letterContent', function(e) {            
            isComposing = true;  // 한글 입력이 시작되었음을 표시하는 변수 설정

            // actionInput 함수 호출 (현재 요소에 대한 처리)
            actionInput($(this));
        });

        // 한글 입력 완료를 감지하는 이벤트 핸들러
        $('#letter').on('compositionend', '.letterContent', function(e) {
            isComposing = false;  // 한글 입력이 완료되었으므로 isComposing을 false로 설정

            let textarea = $(this),
                lineHeight = parseInt(textarea.css('line-height'), 10),  // line-height 값을 가져와서 줄 높이를 계산
                cursorPosition = getCaretCoordinates(textarea[0], textarea.prop('selectionStart')),  // 커서의 좌표를 계산
                savedLines = Math.ceil(cursorPosition.top / lineHeight),  // 현재 텍스트가 몇 번째 줄에 위치하는지 계산
                maxLines = letterList[letterIndex]['maxLine'],  // 최대 줄 수
                savedCursorPosition = this.selectionStart,  // 입력이 끝난 커서 위치
                index = parseInt(textarea.data('index'));  // 현재 입력 필드의 인덱스

            actionInput($(this));  // 입력 처리 함수 호출

            // 현재 줄 수가 최대 줄 수를 초과하고, 커서 위치가 텍스트 길이의 끝에 가까운 경우
            let isLast = (savedLines > maxLines) && (savedCursorPosition - 1) == textarea.val().length;

            // 마지막 줄이라면, 새로운 텍스트 입력 폼을 추가하고 다음 텍스트 영역으로 포커스를 이동
            if(isLast) {
                if ((index + 1) == $('.letterContent').length) {
                    addLetterForm();  // 새로운 입력 필드 추가
                }

                let nextTextarea = $('.letterContent').eq(index + 1);  // 다음 텍스트 필드를 선택

                // 포커스 이동 및 스크롤 처리
                setTimeout(function() {
                    nextTextarea.focus();  // 다음 텍스트 필드에 포커스 설정
                }, 0);   

                // 브라우저가 PC일 경우, 페이지 스크롤 처리
                setTimeout(function() {
                    if(browser == 'pc') {
                        $('html, body').scrollTop(nextTextarea.offset().top - 200);  // 텍스트 필드 위치로 스크롤
                    }
                    nextTextarea[0].setSelectionRange(1, 1);  // 커서 위치 설정
                    nextTextarea.trigger('input');  // input 이벤트 트리거
                }, 100);
            }
        });
        
        // 키를 누를 때 (keydown) 이벤트 처리
        $('#letter').on('keydown', '.letterContent', function(e) {            
            lastKeyEvent = e;  // 마지막 키 이벤트를 저장 (e는 keydown 이벤트 객체)

            actionInput($(this));  // actionInput 함수 호출하여 입력 처리
        });

        // 키를 뗄 때 (keyup) 이벤트 처리
        $('#letter').on('keyup', '.letterContent', function(e) {                        
            actionInput($(this));  // actionInput 함수 호출하여 입력 처리
        });
        
        // 모바일 환경에서 .letterContent 텍스트 영역에 포커스가 가면 실행되는 이벤트
        if(isMobile()) {
            // 텍스트 영역에 포커스가 갈 때
            $('#letter').on('focus', '.letterContent', function(e) {                                                      
                let textarea = $(this),
                    topOffset = textarea.offset().top,  // 텍스트 영역의 위치를 가져옴
                    cursorPosition = getCaretCoordinates(textarea[0], textarea.prop('selectionStart')),  // 커서의 좌표 계산
                    index = parseInt(textarea.data('index'));  // 현재 텍스트 영역의 인덱스                
                
                // 스크롤을 해당 텍스트 영역에 맞게 설정
                setTop(textarea, index, cursorPosition);

                // 헤더와 커스텀 박스를 숨겨서 텍스트 입력에 집중할 수 있게 함
                $('#hd_wrapper, #customBox').addClass('hide');

                // 뷰포트를 확대하여 키보드가 화면을 차지할 수 있도록 설정
                document.getElementById('viewport').setAttribute('content', 'width=device-width, initial-scale=1.0, user-scalable=1, maximum-scale=2');
            });            
            
            // 텍스트 영역에서 포커스가 떨어질 때 (IOS - 정상작동, ANDROID - 이벤트 안먹힘)
            $('#letter').on('blur', '.letterContent', function(e) {
                letterBlurEvent();
            });
            
            if(isAndroid()) {                
                var originalSize = jQuery(window).width() + jQuery(window).height();
     
                // 창의 사이즈 변화가 일어났을 경우 실행된다.
                jQuery(window).resize(function() {
                    // 처음 사이즈와 현재 사이즈가 변경된 경우
                    // 키보드가 올라온 경우
                    if(jQuery(window).width() + jQuery(window).height() != originalSize) {                                                
                    }
                    // 처음 사이즈와 현재 사이즈가 동일한 경우
                    // 키보드가 다시 내려간 경우
                    else {
                       letterBlurEvent();
                    }
                });
            }
        }
        
        // .letterContent 요소에 입력(input) 이벤트가 발생했을 때 실행되는 함수
        $('#letter').on('input', '.letterContent', function(e, customData) {            
            let textarea = $(this), // 현재 이벤트가 발생한 textarea 요소를 저장
                currentText = textarea.val(), // 텍스트 영역의 현재 입력 값을 가져옴
                removedChars = '', // 삭제된 문자 저장 (추후 활용될 수 있음)
                maxHeight = Math.ceil(textarea.height() + 1), // textarea의 높이 + 1 (여백 고려)
                cursorPosition = getCaretCoordinates(textarea[0], textarea.prop('selectionStart')), // 커서의 현재 위치
                lineHeight = parseInt(textarea.css('line-height'), 10), // 줄 간격을 가져옴
                savedLines = Math.ceil(cursorPosition.top / lineHeight), // 현재 커서가 위치한 줄 수를 계산
                maxLines = letterList[letterIndex]['maxLine'], // 최대 줄 수 (letterList 배열에서 가져옴)
                savedCursorPosition = this.selectionStart, // 현재 커서의 위치 저장
                index = parseInt(textarea.data('index')); // textarea의 index를 data에서 가져옴
             
            // 가격 정보를 업데이트
            setPriceInfo();

            // 모바일에서 customData가 없을 경우 스크롤 위치 조정
            if(customData == undefined) {
                setTop(textarea, index, cursorPosition); // 커서 위치에 맞춰 화면 스크롤을 이동
            }
            
            // 이전에 입력된 키 이벤트가 Backspace가 아니면 아래 코드를 실행
            if(lastKeyEvent != null && lastKeyEvent.key === 'Backspace') {
                // Backspace가 눌렸을 때 처리할 로직 (현재는 비어있음)
            }else if (textarea[0].scrollHeight > maxHeight) { // 스크롤 높이가 maxHeight보다 크면 줄 수를 맞추기 위한 처리 시작
                if (!isComposing) { // 한글 입력 중이 아니면
                    // textarea의 높이가 maxHeight보다 클 때 마지막 문자를 제거하여 줄 수를 맞춤
                    while (textarea[0].scrollHeight > maxHeight) {
                        removedChars = currentText.slice(-1) + removedChars; // 마지막 문자를 removedChars에 저장
                        currentText = currentText.slice(0, -1); // 마지막 문자 제거
                        textarea.val(currentText); // textarea의 값을 업데이트
                    }

                    // iOS 브라우저에서는 커서 위치를 다시 맞춰주는 처리
                    if(browser == 'ios' && customData == undefined){
                        textarea[0].setSelectionRange(savedCursorPosition, savedCursorPosition); // 커서 위치 복원
                    }else if(browser != 'ios') {
                        textarea[0].setSelectionRange(savedCursorPosition, savedCursorPosition); // 커서 위치 복원
                    }
                }

                // 마지막 줄에 텍스트가 남은 상태인지 확인
                let isLast = (savedLines > maxLines) && (savedCursorPosition - 1) == textarea.val().length;

                // 마지막 letterContent가 아니면 새로운 letterForm 추가
                if ((index + 1) == $('.letterContent').length) {
                    addLetterForm();
                }

                let nextTextarea = $('.letterContent').eq(index + 1); // 다음 textarea 요소를 선택

                // 마지막 줄에서 줄 수를 맞추었을 때 처리
                if(isLast && customData == undefined){     
                    // 삭제된 문자가 한 줄이라면 공백을 제거하고 추가
                    if (removedChars.length != 1) {
                        removedChars = removedChars.replace(/^\n/, ""); // 맨 앞의 줄바꿈 문자 제거
                        nextTextarea.val(removedChars + nextTextarea.val()); // removedChars를 다음 textarea의 앞에 추가
                    }
                    else {                        
                        // 텍스트를 다음 textarea로 이동하고 커서를 그곳으로 이동
                        setTimeout(function() {                            
                            nextTextarea.focus(); // 포커스를 다음 textarea로 이동
                        }, 0);   

                        setTimeout(function() {
                            if(browser == 'pc') {
                                $('html, body').scrollTop(nextTextarea.offset().top - 200); // PC일 경우 화면을 스크롤
                            }
                            nextTextarea[0].setSelectionRange(0, 0); // 커서를 시작 부분으로 설정
                            nextTextarea.trigger('input'); // input 이벤트를 트리거
                        }, 100);
                    }       
                }else{                    
                    // 삭제된 문자의 맨 앞 공백을 제거
                    removedChars = removedChars.replace(/^\s+/, "");

                    // removedChars에 내용이 남아있다면 그 값을 다음 textarea에 추가
                    if (removedChars.trim() !== "") {
                        nextTextarea.val(removedChars + nextTextarea.val());
                    }

                    // 다음 textarea에 input 이벤트를 트리거
                    nextTextarea.trigger('input', { isFocus : false });
                }    
            }else if(customData != undefined && $('.letterContent').eq(index + 1).length) { // customData가 있고, 다음 textarea가 있다면
                let nextTextarea = $('.letterContent').eq(index + 1);
                nextTextarea.trigger('input', { isFocus : false }); // 다음 textarea에 input 이벤트를 트리거
            }
        });          
        
        // #step-2 내의 .letterBox 요소를 클릭했을 때 실행되는 이벤트
        $('#step-2').on('click', '.letterBox', function(){ 
            // 클릭된 .letterBox 내의 .letterContent 요소를 찾음
            let $target = $(this).find('.letterContent');

            // .letterContent 요소에 포커스를 설정
            $target.focus();

            // 해당 텍스트 영역의 index 값을 selectIndex에 저장
            selectIndex = parseInt($target.data('index'));
        });        
        
        /* 이미지, 파일 JQUERY 처리 영역 */
        $('#photoController').on('change', function() {
            readPhotoImg(this);  // 이미지 파일을 처리하는 함수 호출
        });
        
        $('#fileController, #imgFileController').on('change', function() {
            readFile(this);  // 선택한 파일을 처리하는 함수 호출
        });

        $('#photoDropZone, #dropZone').on('dragover', function (e) {
            e.preventDefault();  // 기본 드래그 동작을 방지
            $(this).addClass('dragover');  // 드래그 중인 영역에 클래스를 추가하여 스타일 변경
        });

        $('#photoDropZone, #dropZone').on('dragleave', function () {
            $(this).removeClass('dragover');  // 드래그 중이 아니므로 스타일을 원래대로 복원
        });

        $('#photoDropZone, #dropZone').on('drop', function (e) {
            e.preventDefault();  // 기본 동작을 방지
            $(this).removeClass('dragover');  // 드래그 오버 스타일 제거
            const files = e.originalEvent.dataTransfer.files;  // 드래그한 파일들            
            
            /* 포토 드래그 */
            if($(this)[0].id == 'photoDropZone') {
                readPhotoImg({ files }); // 기존 readPhotoImg 함수 재사용
            }else { /* 파일 드래그 */
                readFile({ files });  // 기존 readFile 함수 재사용   
            }
        });
        
        
        /* 포인트 입력 JQUERY 처리 영역 */        
        $('#payPoint').on('keyup', function(){
            let $el = $(this),
                myPoint = parseInt($('#myPoint').val()),  // 보유 포인트
                totalPrice = parseInt($('#realTotalPrice').val()),  // 결제 금액
                setPrice = uncomma($el.val());  // 입력한 포인트 값에서 쉼표 제거

            // 입력한 포인트가 보유 포인트보다 많은 경우
            if(setPrice > myPoint){
                showAlert('보유 포인트보다 많은 포인트를 사용하실 수 없습니다.', deletePoint());
                return false;
            }
            // 입력한 포인트가 결제 금액보다 많은 경우
            else if(setPrice > totalPrice){
                showAlert('결제금액보다 많은 포인트를 사용하실 수 없습니다.', deletePoint());
                return false;
            }

            // 입력한 포인트가 보유 포인트나 결제 금액과 같은 경우
            if(setPrice == myPoint || setPrice == totalPrice){                
                $('#allPayBtn').addClass('active');
            }else{
                $('#allPayBtn').removeClass('active');
            }

            // 0을 제거하고, 쉼표 추가하여 입력 필드에 다시 설정
            $el.val(comma(setPrice.replace(/^0+/, '')));

            // 결제 정보 갱신
            setPriceInfo();
        });

		$(document).on('click', '.spinner .arrow.up', function () {
			const $fileWrap = $(this).closest('.fileWrap.libraryFile');
			const libIdx = $fileWrap.data('lib-idx');
			const library = selectedLibraries.find(lib => parseInt(lib.idx) === libIdx);
			if (library) {
				library.libCount += 1;
				$fileWrap.find('.number').text(library.libCount);
				setPriceInfo();
			}
		});

		$(document).on('click', '.spinner .arrow.down', function () {
			const $fileWrap = $(this).closest('.fileWrap.libraryFile');
			const libIdx = $fileWrap.data('lib-idx');
			const library = selectedLibraries.find(lib => parseInt(lib.idx) === libIdx);
			if (library && library.libCount > 1) {
				library.libCount -= 1;
				$fileWrap.find('.number').text(library.libCount);
				setPriceInfo();
			}
		});
        
        defaultSetup();        
    });



</script>



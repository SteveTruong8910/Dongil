<style>
    #notice table td{ font-size: 13px; }
    #container{  overflow-x: auto; }
    table { width: 2700px !important; }
    .chkBtn{ display: block; margin-top: -1px; color: #4444f7; border-radius: 5px; padding: 3px 6px;  cursor: unset; }

    .summary-table {
        width: 100% !important;
        table-layout: fixed;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .summary-table th, .summary-table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
        white-space: normal;
        width: auto;
    }

    .summary-table th:nth-child(3), .summary-table td:nth-child(3),
    .summary-table th:nth-child(7), .summary-table td:nth-child(7),
    .summary-table th:nth-child(12), .summary-table td:nth-child(12),
    .summary-table th:nth-child(13), .summary-table td:nth-child(13),
    .summary-table th:nth-child(14), .summary-table td:nth-child(14) {
        width: 100px;
    }

    .summary-table th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }

    #orderSummaryModal .modal-dialog {
        max-width: 90%;
        width: 1800px !important;
    }

    #orderSummaryModal .modal-content {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

</style>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=<?=$this->config->item('kakaoJsKey')?>&libraries=services"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.14.3/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
<script src="https://unpkg.com/pdf-lib@1.17.1"></script>
<script src="https://unpkg.com/downloadjs@1.4.7"></script>

<p class="title">주문 관리</p>

<div id="notice">    
    <div class="local">
        총 주문 수 <?=$pageData['totalCnt']?>건
    </div>
     <form id="searchForm" method="get">
        <div class="filter">
            <input type="hidden" name="page" value="1"/>
                <div id="orderSummaryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" style="width: 500px;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <strong>발송 현황</strong>
                                <i class="fas fa fa-times" onclick="closeModal('orderSummaryModal')"></i>
                            </div>
                            <div class="modal-body">
                                <table class="summary-table">
                                    <thead>
                                        <tr id="summaryHeaderRow"></tr>
                                    </thead>
                                    <tbody>
                                        <tr id="summaryDataRow"></tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            
            <?
                $searchTypeArr = [
                    "W.senderName" => '주문자 성함',
                    "W.senderTel" => '주문자 전화번호',
                    "W.senderAddr" => '주문자 주소',
                    "W.senderAddrDetail" => '주문자 상세주소',
                    "W.receiverTel" => '받는분 전화번호',
                    "W.receiverName" => '받는분 성함',
                    "W.receiverAddr" => '받는분 주소',
                    "W.receiverAddrDetail" => '받는분 상세주소',
                    "W.memberIdx" => '회원번호',
                    "W.excelDownDate" => '엑셀다운시간',
                ];
            ?>

            <select id="searchType" name="searchType" class="searchType">
                <? foreach($searchTypeArr as $data => $name){ ?>
                    <option value="<?=$data?>" <?=$data == $searchData['searchType']? 'selected' : ''?>><?=$name?></option>
                <? } ?>
            </select>
            
            <input type="text" id="searchTxt" name="searchTxt" class="searchTxt" value="<?=$searchData['searchTxt']?>" style="margin-bottom: 15px;"/>
            <i class="fas fa-search" onclick="searchForm()"></i>
            
            
            <select id="pagingCnt" name="pagingCnt" class="searchType <?=$isAllView == 'Y'? 'hide' : ''?>" style="margin-left:20px;" onchange="searchForm()">
                <? foreach($this->config->item('pagingCnt') as $data => $name){ ?>
                    <option value="<?=$data?>" <?=$data == $searchData['pagingCnt']? 'selected' : ''?>><?=$name?></option>
                <? } ?>
            </select>
            <button type="button" class="btnDownload" style="margin-left:20px;"onclick="openModal('orderSummaryModal')">발송 현황</button>
            <br>
            <strong> ☑주문상태 / </strong>
            <? foreach($this->config->item('state') as $stateKey => $name){ 
                if($stateKey == 'T') continue;
            ?>
                <input type="radio" id="state<?=$stateKey?>" name="state" value="<?=$stateKey?>" <?=$searchData['state'] == $stateKey? 'checked' : ''?> onclick="searchForm()" data-name="<?=$name?>">
                <label for="state<?=$stateKey?>"><?=$name?></label>
            <? } ?>
            
            <strong style="margin-left:10px;"> ☑일자 / </strong>
                        
            <? foreach($this->config->item('dateState') as $stateKey => $name){ ?>
                <input type="radio" id="date<?=$stateKey?>" name="dateState" value="<?=$stateKey?>" <?=$searchData['dateState'] == $stateKey? 'checked' : ''?> onclick="changeDateState('dateState')">
                <label for="date<?=$stateKey?>"><?=$name?></label>
            <? } ?>
            <div id="dateStateBox" class="<?=$searchData['dateState'] == 'CHOICE'? '' : 'hide'?>" style="display:inline-block;">
                <input type="date" name="startDate" value="<?=$searchData['startDate']?>" max="<?=date('Y-m-d')?>" style="border:1px solid black; border-radius: 5px;height: 31px; padding-left:5px;"/>
                <input type="date" name="endDate" value="<?=$searchData['endDate']?>" max="<?=date('Y-m-d')?>" style="border:1px solid black; border-radius: 5px;height: 31px; padding-left:5px;"/>
                <button class="btnDownload" onclick="searchForm()">검색</button>
            </div>
            
            <? if($searchData['state'] == 'F') { ?>
                <strong style="margin-left:10px;"> ☑발송일자 / </strong>
                        
                <? foreach($this->config->item('dateState') as $stateKey => $name){ ?>
                    <input type="radio" id="finishDate<?=$stateKey?>" name="finishDateState" value="<?=$stateKey?>" <?=$searchData['finishDateState'] == $stateKey? 'checked' : ''?> onclick="changeDateState('finishDateState')">
                    <label for="finishDate<?=$stateKey?>"><?=$name?></label>                                
                <? } ?>
                <div id="finishDateStateBox" class="<?=$searchData['finishDateState'] == 'CHOICE'? '' : 'hide'?>" style="display:inline-block;">
                    <input type="date" name="finishStartDate" value="<?=$searchData['finishStartDate']?>" max="<?=date('Y-m-d')?>" style="border:1px solid black; border-radius: 5px;height: 31px; padding-left:5px;"/>
                    <input type="date" name="finishEndDate" value="<?=$searchData['finishEndDate']?>" max="<?=date('Y-m-d')?>" style="border:1px solid black; border-radius: 5px;height: 31px; padding-left:5px;"/>
                    <button class="btnDownload" onclick="searchForm()">검색</button>
                </div>
            <? } ?>
        
            <br>            
            <strong style="margin-top: 10px;"> ☑컬러 / </strong>

            <? foreach($this->config->item('color') as $colorKey => $name){ ?>
                <input type="radio" id="color<?=$colorKey?>" name="color" value="<?=$colorKey?>" <?=$searchData['color'] == $colorKey? 'checked' : ''?> onclick="searchForm()">
                <label for="color<?=$colorKey?>"><?=$name?></label>
            <? } ?>
                        
            <strong style="margin-left:10px;"> ☑우편 / </strong>

            <? foreach($this->config->item('admStamp') as $stampKey => $name){ ?>
                <input type="radio" id="stamp<?=$stampKey?>" name="stamp" value="<?=$stampKey?>" <?=$searchData['stamp'] == $stampKey? 'checked' : ''?> onclick="searchForm()"  data-name="<?=$name?>">
                <label for="stamp<?=$stampKey?>"><?=$name?></label>
            <? } ?>
            
            <br>            
            <strong style="margin-top: 10px;"> ☑봉투 / </strong>
            <? foreach($this->config->item('envelope') as $envelopeKey => $name){ ?>
                <input type="radio" id="envelope<?=$envelopeKey?>" name="envelope" value="<?=$envelopeKey?>" <?=$searchData['envelope'] == $envelopeKey? 'checked' : ''?> onclick="searchForm()">
                <label for="envelope<?=$envelopeKey?>"><?=$name?></label>
            <? } ?>
<!-- 
            <strong style="margin-left:10px;"> ☑봉투 무게 / </strong>
            <? foreach ($this->config->item('envelopeWeight') as $envelopeWeightKey => $name) { ?>
                <input type="radio" id="envelopeWeight<?=$envelopeWeightKey?>" name="envelopeWeight" value="<?=$envelopeWeightKey?>" 
                      <?= $searchData['envelopeWeight'] == $envelopeWeightKey ? 'checked' : '' ?> onclick="searchForm()">
                <label for="envelopeWeight<?=$envelopeWeightKey?>"><?=$name?></label>
            <? } ?> -->
        </div>
    </form>
    
    <? if(count($list) > 0){ ?>
        <span>
            ☑            
            <button class="btnDownload greyDownload" style="background: #797979; color: #fff;" onclick="allPrinting('Post');">편지 일괄출력</button>
            <button class="btnDownload greyDownload" style="background: #797979; color: #fff;" onclick="allPrinting('Sign');">봉투 일괄출력</button>
            <button class="btnDownload greyDownload" style="background: #797979; color: #fff;" onclick="allPrinting('Image?isGloss=Y');">유광이미지 일괄출력</button>
            <button class="btnDownload greyDownload" style="background: #797979; color: #fff;" onclick="allPrinting('Image?isGloss=N');">무광이미지 일괄출력</button>
            <button class="btnDownload" style="background: #797979; color: #fff;" onclick="allFileDown();">문서 일괄다운</button>
            <button class="btnDownload" style="background: #797979; color: #fff;" onclick="allLibraryDown();">자료 일괄다운</button>
            <button class="btnDownload greyDownload" style="background: #797979; color: #fff;" onclick="allPrinting('Sign?big=Y');">대봉투 일괄출력</button>
        </span>
        <span>
            ☑
            <button class="btnDownload" onclick="exportExcel()" style="background: #03a603; color: #fff;">📁엑셀 다운로드(.xlsx)</button>
            <button class="btnDownload" onclick="downloadAllCashReceipts()" style="background: #03a603; color: #fff; padding: 5px;">현금영수증 일괄 다운로드</button>
        </span>                     
        
        <div style="margin-top:5px;">                                  
            <span>
                ☑                
                <select id="stateChange" style="border: 1px solid black; padding: 3px 2px; border-radius: 5px;">
                    <? 
                        $changeState = $this->config->item('state');
                                                
                        if($searchData['state'] == 'P') {
                            $changeState = array_reverse($changeState);
                        }
                        
                        foreach($changeState as $stateKey => $name){
                            if($stateKey != 'R' && $stateKey != 'S' && $stateKey != 'I' && $stateKey != 'P' && $stateKey != 'F' && $stateKey != 'W') continue;
                    ?>
                            <option value="<?=$stateKey?>"><?=$name?></option>
                    <? } ?>
                </select>                
                <button class="btnDownload greyDownload" onclick="batchSubmit()" style="">선택 일괄변경</button>
                <input type="file" id="batchFileToUpload" class="hide" accept=".xls,.xlsx" onchange="readBatchExcel()">
                <button class="btnDownload" onclick="allBatchSubmit()" style="background: #00b192; color: #fff;">📤엑셀 업로드(일괄발송)</button>
                <a class="btnDownload" href="/assets/file/일괄발송_업로드샘플.xls" style="background: #f2ffbe;" download>🌞일괄발송 업로드샘플</a>                
            </span>
            <? if(($searchData['state'] == 'S' || $searchData['state'] == 'I' || $searchData['state'] == 'R') && (int)$searchData['stamp'] >= 1 && (int)$searchData['stamp'] != 99) { ?>
            <span>
                ☑
                <button class="btnDownload" onclick="excelUpload()" style="background: #00b192; color: #fff;">📤엑셀 업로드(등기번호)</button>
                <a class="btnDownload" href="/assets/file/등기번호_업로드샘플.xls" style="background: #f2ffbe;" download>🌞등기번호 업로드샘플</a>
            </span>
            <? } ?>
        </div>

        <div style="margin-top:5px;">
            <span>
                ☑
                <button class="btnDownload" style="background: #dcdcdc;" onclick="lockSelectedOrders()">🔒 선택 주문 락걸기</button>
                <button class="btnDownload" onclick="unlockSelectedOrders()">🔓 선택 주문 락해제</button>
            </span>
        </div>
    <? } ?>
    
    <table style="margin-top:10px;">
        <thead>
            <tr>                                
                <th width="30"><input type="checkbox" id="allBatch" value="Y" onclick="batchProcess('all', $(this));"></th>                
                <th width="50">번호</th>
                <th width="120">주문번호</th>                
<!--                <th>상품명</th>-->                
                <th width="300">주문자</th>
                <th width="260">받는사람</th>
                <th>편지지 금액</th>
                <th>사진 금액</th>
                <th>문서 금액</th>
                <th>자료 금액</th>
                <th>우편 금액</th>
                <th>
                    <p>총 결제금액</p>
                    <p>(실결제/포인트)</p>
                </th>
                <th>봉투 무게</th>
                <th width="80">편지지</th>
                <th width="80">봉투</th>
                <th width="80">유광</th>
                <!-- <th width="80">무광</th> -->
                <th width="80">문서</th>
                <th width="80">자료</th>
                <th width="80">대봉투</th>
                <th>상태</th>
                <th>나이스페이 결제번호</th>                
                <th>엑셀다운시간</th>                
                <th>등기번호</th>
                <th width="130">결제타입</th>
                <th width="110">발송일자</th>
                <th width="110">주문일자</th>
            </tr>
        </thead>
        <tbody>
            <? if(!count($list)){ ?>
                <tr>
                    <td colspan="22" class="empty">주문 내역이 존재하지않습니다.</td>
                </tr>
            <? } ?>
            
            <? foreach($list as $key => $data){ ?>
                <tr style="<?= 
                    ($data['isLocked'] === 'Y') 
                        ? 'background: #dcdcdc !important;' 
                        : (!containsSaseoham($data['receiverAddr']) && !containsSaseoham($data['receiverAddrDetail']) 
                            ? 'background: #fff3f3 !important;' 
                            : ($data['prev_saveCount'] !== null && $data['prev_saveCount'] !== '' && $data['isSaveCnt'] > $data['prev_saveCount'] 
                                ? 'background: #ff6666 !important;' 
                                : '')) 
                ?>"

                  <?=($data['prev_saveCount'] !== null && $data['prev_saveCount'] !== '' && $data['isSaveCnt'] > $data['prev_saveCount']) ? 'background: #ff6666 !important;' : '' ?>">
                    <td><input type="checkbox" class="subBatch" value="<?=$data['idx']?>" onclick="batchProcess('sub', $(this));"></td>
                    <td><?=$data['idx']?></td>    
                    <td>
                        <a style="cursor: pointer; text-decoration: underline;" href="/admin/member?searchType=idx&searchTxt=<?=$data['memberIdx']?>"><?=$data['writeId']?></a>
                    </td>
<!--                    <td><?=$data['productName']?></td>-->
                    <td class="left">
                        <a onclick="openAddrModal(<?=$key?>, 'sender')" style="text-decoration: underline;">
                            <p><?=removePostboxNumbers($data['senderAddr'])?> <?=$data['senderAddrDetail']?></p>                        
                            <p><?=$data['senderName']?> / <?=$data['senderTel']?></p>
                        </a>
                    </td>
                    <td class="left">
                        <a onclick="openAddrModal(<?=$key?>, 'receiver')" style="text-decoration: underline;">
                            <p><?=removePostboxNumbers($data['receiverAddr'])?> <?=$data['receiverAddrDetail']?></p>                        
                            <p><?=$data['receiverName']?> / <?=$data['receiverTel']?></p>                        
                        </a>
                    </td>
                    <td>
                        <p><?=$data['productName']?> * <?=$data['totalLetterCnt']?>장</p>
                        <p><?=number_format($data['letterPrice'])?>원</p>
                    </td>
                    <td>
                        <p><?=$data['totalPhotoCnt']?>장 / <?=number_format($data['photoPrice'])?>원</p>
                    </td>
                    <td>
                        <p><?=$data['totalPdfFileCnt']?>개 / <?=number_format($data['filePrice'])?>원</p>
                    </td>
                    <td>
                        <p><?=$data['totalLibraryFileCnt']?>개 / <?=number_format($data['libraryPrice'])?>원</p>
                    </td>
                    <td>
                        <p><?=$this->config->item('stamp')[$data['stamp']]['name']?></p>
                        <p><?=number_format($data['stampPrice'])?>원</p>
                    </td>
                    <td>
                        <p><?=number_format($data['realTotalPrice'])?>원 / <?=number_format($data['payPoint'])?>원</p>
                        <p><?=number_format($data['totalPrice'])?>원</p>
                    </td>
                    <td>
                        <p><?=$data['envelopeWeight']?>g</p>
                    </td>
                    <td>
                        <? if($data['totalLetterCnt'] > 0) { ?>
                            <button class="btnDownload allLetterBtn" data-idx="<?=$data['idx']?>"  onclick="downLetter(<?=$data['idx']?>)">편지출력</button>
                        <? }else { ?>
                            <p>편지 등록X</p>
                        <? } ?>
                    </td>
                    <td>
                        <? if((int)$data['totalLetterCnt'] + (int)$data['totalPhotoCnt'] >= $this->config->item('maxBigCnt') || $data['totalPdfFileCnt'] > 0 || $data['totalLibraryFileCnt'] > 0) { ?>
                            <span>대봉투</span>
                        <? }else if(!empty($data['registrationNumber']) || $data['stamp'] == 0) { ?>
                            <button class="btnDownload allSignBtn" data-idx="<?=$data['idx']?>"  onclick="downSign(<?=$data['idx']?>, 'N')">봉투출력</button>
                        <? }else { ?>
                            <span>운송장 미기입</span>
                        <? } ?>
                    </td>
                    <td>
                        <? if($data['isGlossCnt'] > 0){ ?> 
                        <button class="btnDownload allImgBtn" data-idx="<?=$data['idx']?>" onclick="downImg(<?=$data['idx']?>, 'Y')">유광</button>
                        <? }else{ ?>
                        <p>이미지 등록X</p>
                        <? } ?>
                    </td>
                    <!-- <td>
                        <? if($data['isNoneGlossCnt'] > 0){ ?> 
                        <button class="btnDownload allImgBtn" data-idx="<?=$data['idx']?>" onclick="downImg(<?=$data['idx']?>, 'N')">무광</button>
                        <? }else{ ?>
                        <p>이미지 등록X</p>
                        <? } ?>
                    </td> -->
                    <td>
                        <? if($data['totalPdfFileCnt'] > 0) { ?>
                        <button class="btnDownload allFileBtn" data-idx="<?=$data['idx']?>" onclick="downFile(<?=$key?>)">문서다운</button>
                        <? }else { ?>
                        <p>문서 등록X</p>
                        <? } ?>
                    </td>
                    <td>
                        <? if($data['totalLibraryFileCnt'] > 0) { ?>
                        <button class="btnDownload allLibraryBtn" data-idx="<?=$data['idx']?>" onclick="downLibrary(<?=$key?>)">자료다운</button>
                        <? }else { ?>
                        <p>자료 신청X</p>
                        <? } ?>
                    </td>
                    <td>
                        <? if((int)$data['totalLetterCnt'] + (int)$data['totalPhotoCnt'] >= $this->config->item('maxBigCnt') || $data['totalPdfFileCnt'] > 0 || $data['totalLibraryFileCnt'] > 0) { ?>
                        <button class="btnDownload allBigSignBtn" data-idx="<?=$data['idx']?>" onclick="downSign(<?=$data['idx']?>, 'Y')">대봉투</button>
                        <? }else if(!empty($data['registrationNumber']) && $data['totalPdfFileCnt'] > 0){ ?>
                        <p>운송장 등록X</p>
                        <? }else { ?>
                        <p>문서 등록X</p>
                        <? } ?>
                    </td>
                    <td>
                        <select onchange="changeState($(this));" data-idx="<?=$data['idx']?>"
                            <?=($data['isLocked'] === 'Y' && $data['state'] != 'C') ? 'disabled' : ''?>>
                            <? foreach($this->config->item('state') as $stateKey => $name){ 
                                if($stateKey == 'A') continue;
                            ?> 
                                <option value="<?=$stateKey?>" <?=$stateKey == $data['state']? 'selected': ''?>><?=$name?></option>
                            <? } ?>
                        </select>
                    </td>
                    <td>dongl-<?=$data['idx']?></td>                    
                    <td>
                        <span class="excelDownDate" data-idx="<?=$data['idx']?>" style="color:<?=$data['excelDownDate'] != '00000000000000'? 'green' : ''?>">
                            <?=$data['excelDownDate']?>
                        </span>
                        <button onclick="setExcelDownDate(<?=$data['idx']?>, '<?=$data['excelDownDate']?>')" style="margin-top: 2px; border-radius:5px; padding: 3px;">
                            다운시간수정
                        </button>
                    </td>                    
                    <td>
                        <span class="registrationNumber" data-idx="<?=$data['idx']?>"><?=$data['registrationNumber']?></span>                        
                        <button onclick="setRegistrationNumber(<?=$data['idx']?>, '<?=$data['registrationNumber']?>')" style="margin-top: 2px; border-radius:5px; padding: 3px;">
                            등기번호수정
                        </button>
                    </td>
                    <td>
                        <p><?=$this->config->item('payType')[$data['payType']]?></p>
                        <? if($data['state'] == 'B'){ ?>
                        
                            <? if($data['isPay'] == 'N') { ?> 
                                <a class="chkBtn">입금대기 확인요망</a>
                            <? } ?>
                        
                            <? if($data['isCashReceipt'] == 'Y' && $data['isPay'] == 'Y' && !in_array($data['state'], ['T', 'B', 'C'])) { ?>
                                <button onclick="downCashReceipt(<?=$key?>)" style="background: #03a603; color: #fff; padding: 5px;">현금영수증</button>
                            <? } ?>
                        <? } ?>

                        <? if($data['isCashReceipt'] == 'Y' && $data['isPay'] == 'Y' && !in_array($data['state'], ['T', 'B', 'C'])) { ?>
                                <button onclick="downCashReceipt(<?=$key?>)" style="background: #03a603; color: #fff; padding: 5px;">현금영수증</button>
                        <? } ?>
                    </td>
                    <td><?=$data['finishDate']?></td>
                    <td><?=$data['regDate']?></td>
                </tr>
            <? } ?>
        </tbody>
    </table>
    
    <? if($isAllView == 'N'){ ?>
        <? $this->load->view('/common/page', $pageData); ?>
    <? } ?>
</div>


<div id="addrModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 500px;">
        <div class="modal-content">
            <div class="modal-body" style="overflow: hidden;">
                <i class="fas fa fa-times" onclick="closeAddrModal()"></i>                    
                
                <strong>주소지수정</strong>                                
                <div class="addrBtnWrap">
                    <button class="addrBtn" onclick="openAddrDetailModal('show', 'army')">전국 군대 훈련소</button>
                    <button class="addrBtn" onclick="openAddrDetailModal('show', 'prison')">전국 구치소/교도소/소년원</button>
                </div>                
                <input type="hidden" id="addrIndex" value="">
                <input type="hidden" id="addrType" value="">
                
                <input type="hidden" id="zipCode"/>
                <p class="guide">주소</p>
                <input type="text" id="addr" class="fieldInput" value="" placeholder="주소" onclick="openDaumPostcode($('#zipCode'), $('#addr'), $('#addrDetail'), 1)" readonly/>
                <p class="guide">상세주소</p>
                <input type="text" id="addrDetail" class="fieldInput" value="" placeholder="상세주소"/>
                <p class="guide">이름</p>
                <input type="text" id="name" class="fieldInput" value="" placeholder="이름"/>
                <p class="guide">전화번호</p>
                <input type="text" id="tel" class="fieldInput" value="" placeholder="전화번호" oninput="this.value = this.value.replace(/[^0-9]/g, '');"/> 
                
                <button class="btnSubmit" onclick="saveAddr()">저장하기</button>
            </div>                        
        </div>
    </div>
</div>

<div id="addrDetailModal" class="postPopup popupContainer">
    <div class="popupBox">
        <div class="title">
            <p id="addrTitle">전국 군대 훈련소</p>
            <i class="fas fa-times" onclick="openAddrDetailModal('close');"></i>
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

<div id="excelModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 1000px;">
        <div class="modal-content">
            <div class="modal-body" style="overflow: hidden;">
                <i class="fas fa fa-times" onclick="closeExcelModal()"></i>
                
                <input type="file" name="fileToUpload" id="fileToUpload" class="hide" accept=".xls,.xlsx" onchange="readExcel()">
                <div style="display: flex; justify-content: space-between; margin: 25px;">
                    <span style="min-width: 300px; text-align: center; border: 1px solid #636363; padding: 7px; border-radius: 5px;">
                        <p style="text-align: center; font-weight: bold; font-size: 18px;">준등기</p>
                        <p id="excelFileName1">준등기.xlsx</p>
                    </span>
                    <span style="min-width: 300px; text-align: center; border: 1px solid #636363; padding: 7px; border-radius: 5px;">
                        <p style="text-align: center; font-weight: bold; font-size: 18px;">일반등기</p>
                        <p id="excelFileName2">일반등기.xlsx</p>
                    </span>
                    <span style="min-width: 300px; text-align: center; border: 1px solid #636363; padding: 7px; border-radius: 5px;">
                        <p style="text-align: center; font-weight: bold; font-size: 18px;">익일특급</p>
                        <p id="excelFileName3">익일특급.xlsx</p>
                    </span>                                        
                </div>
                <button id="dropZone" class="btnDownload" onclick="$('#fileToUpload').click()" style="width: 100%; padding: 12px 0px; border: 1px dashed black; background: #f0fff0;">
                    <p>📤엑셀 업로드(등기번호)</p>
                    <p>파일을 해당영역에 드래그 또는 클릭하여 문서 업로드</p>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // PHP에서 전달된 데이터를 JavaScript 변수로 변환
    var postList = <?=json_encode($list)?>;  // 게시글 목록
    var lockedItem = [];  // 게시글 목록
    const payType = <?=json_encode($this->config->item('payType'))?>;  // 결제 유형 설정
    const stamp = <?=json_encode($this->config->item('stamp'))?>;  // 우표 설정
    const admStamp = <?=json_encode($this->config->item('admStamp'))?>;  // 관리자 우편 설정
    const state = <?=json_encode($this->config->item('state'))?>;  // 상태 설정
    const envelope = <?=json_encode($this->config->item('envelope'))?>;  // 봉투 설정
    
    const colorConfig = <?= json_encode($this->config->item('color')) ?>;
    const color = '<?=$searchData['color']?>';  // 검색 데이터에서 색상
    const pageState = '<?=$searchData['state']?>';  // 검색 데이터에서 상태

    /* 주소 관련 설정 */
    const prisonType = <?=json_encode($this->config->item('prisonType'))?>;  // 감옥 유형
    const prison = <?=json_encode($this->config->item('prison'))?>;  // 감옥 리스트
    const armyType = <?=json_encode($this->config->item('armyType'))?>;  // 군대 유형
    const army = <?=json_encode($this->config->item('army'))?>;  // 군대 리스트
    var addressType = { prison: prisonType, army: armyType };  // 주소 유형 객체
    var addressList = { prison: prison, army: army };  // 주소 리스트 객체

    // PDF 관련 설정 (PDFLib 사용)
    const { PDFDocument } = PDFLib;    


    // 날짜 상태 변경 함수
    function changeDateState(dateStateType) {
        let dateState = $(`input[name="${dateStateType}"]:checked`).val();

        if (dateState == 'ALL') {
            searchForm();  // 모든 상태에 대해 검색
        } else {            
            $(`#${dateStateType}Box`).removeClass('hide');  // 특정 날짜 상태 박스 보이기
        }
    }

    // 주소 모달 열기 함수
    function openAddrModal(index, type) {
        let data = postList[index];  // postList에서 선택한 데이터 가져오기

        // 모달에 데이터를 채운 후, 모달을 표시
        $('#addrIndex').val(index);
        $('#addrType').val(type);
        $('.guide1').remove();  // 이전 안내 메시지 삭제
        $('#addr').val(data[`${type}Addr`]);  // 주소 설정
        $('#addrDetail').val(data[`${type}AddrDetail`]);  // 상세 주소 설정
        $('#name').val(data[`${type}Name`]);  // 이름 설정
        $('#tel').val(data[`${type}Tel`]);  // 전화번호 설정

        $('#addrModal').modal('show');  // 주소 모달 표시
    }

    // 주소 모달 닫기 함수
    function closeAddrModal() {
        $('#addrModal').modal('hide');  // 주소 모달 숨기기
    }

    // 주소 저장 함수 (비동기 처리)
    async function saveAddr() {
        // 저장 확인 팝업
        if (!confirm('해당 변경사항을 저장하시겠습니까?')) return;

        let index = parseInt($('#addrIndex').val()),  // 선택된 인덱스
            type = $('#addrType').val(),  // 주소 유형
            data = postList[index];  // 해당하는 게시글 데이터 가져오기

        // 주소 변경 정보를 API로 전송하여 저장
        const saveAddrRes = await postJson('/adminApi/saveAddr', {
            idx: data.idx,  // 데이터 idx
            memberIdx: data.memberIdx,  // 회원 idx
            type: type,  // 주소 유형
            addr: $('#addr').val(),  // 주소
            addrDetail: $('#addrDetail').val(),  // 상세 주소
            name: $('#name').val(),  // 이름
            tel: $('#tel').val()  // 전화번호
        });

        // 저장 결과 확인
        if (!saveAddrRes.result) {
            showAlert(saveAddrRes.msg);  // 실패 메시지 표시
            return;
        }

        location.reload();  // 페이지 새로고침하여 변경사항 반영
    }

    
    // 배치 처리 함수
    // type: 'all'일 경우 전체 선택/해제를, 그 외의 경우에는 선택된 항목에 따라 전체 선택 여부를 결정
    function batchProcess(type, $this) {
        // 전체 선택 체크박스, 서브 항목 체크박스들, 체크된 서브 항목들 변수 선언
        let $allBatch = $('#allBatch'),
            $subBatch = $('.subBatch'),
            $subChkBatch = $('.subBatch:checked'),
            subLength = $subBatch.length,  // 서브 항목의 총 개수
            subChkLength = $subChkBatch.length;  // 체크된 서브 항목의 개수

        // 'all'인 경우: 전체 항목을 체크박스와 동일하게 설정
        if(type == 'all'){
            $subBatch.prop('checked', $allBatch.is(':checked'));
        }else{
            // 선택된 항목 수가 총 항목 수와 같으면 전체 선택 체크박스를 체크하고, 그렇지 않으면 해제
            if(subLength == subChkLength){
                $allBatch.prop('checked', true);
            }else{
                $allBatch.prop('checked', false);
            }
        }
    }

    // 일괄 상태 변경 함수
    async function batchSubmit(){
        // 체크된 서브 항목들을 가져옴
        let $subChkBatch = $('.subBatch:checked'),
            writeIdxArr = [],  // 선택된 항목들의 idx를 담을 배열
            prevSaveCounts = {},
            state = $("#stateChange option:selected").val();  // 선택된 상태 값

        // 락 걸린 항목 체크
        let hasLocked = false;

        // 선택된 항목들의 idx를 배열에 추가
        for(let i=0; i<$subChkBatch.length; i++){
            let idx = $subChkBatch.eq(i).val();
            let orderData = postList.find(item => item.idx == idx);

            if (!orderData) {
                showAlert("해당 데이터를 찾을 수 없습니다.");
                return;
            }
            
            // 🔒 락 걸린 항목은 제외
            if (orderData.isLocked === 'Y' && orderData.state !== 'C') continue;

            writeIdxArr.push(idx);

            if (state === 'S') {
                prevSaveCounts[idx] = orderData.isSaveCnt || 0;
            }
        }

         // 락 걸린 항목이 하나라도 있으면 막기
        if (hasLocked) {
            showAlert("🔒 락이 걸린 항목이 포함되어 있어 상태 변경할 수 없습니다.");
            return;
        }

        // 선택된 항목이 없으면 경고 메시지 출력
        if(!writeIdxArr.length) {
            showAlert("변경하실 항목을 선택해주세요.");
            return;
        }

        // 서버로 상태 변경 요청 전송
        const changeStateRes = await postJson('/adminApi/changeAllState', {
            writeIdxArr : writeIdxArr,
            state : state,
            prevSaveCounts: prevSaveCounts
        });

        // 서버 응답 결과가 실패일 경우 경고 메시지 출력
        if(!changeStateRes.result){
            showAlert(changeStateRes.msg);
            return;
        }

        // 성공적으로 상태 변경되면 페이지 새로 고침
        location.reload();
    }
    
    // 일괄 출력 함수
    function allPrinting(type) {           
        // 체크된 서브 항목들을 가져옴
        let $subChkBatch = $('.subBatch:checked');        

        // 출력할 항목이 없으면 경고 메시지 출력 후 종료
        if (!$subChkBatch.length) {
            showAlert("출력하실 항목을 선택해주세요.");
            return;
        }

        let writeIdxArr = [];

        for (let i = 0; i < $subChkBatch.length; i++) {
            let idx = $subChkBatch.eq(i).val();
            let orderData = postList.find(item => item.idx == idx);

            if (!orderData) continue;

            // 🔒 락 걸린 항목은 제외
            if (orderData.isLocked === 'Y' && orderData.state !== 'C') continue;

            writeIdxArr.push(idx);
        }

        if (!writeIdxArr.length) {
            showAlert("출력할 수 있는 항목이 없습니다.");
            return;
        }


        // 🏣 봉투 출력인 경우 우체국 선택 모달 띄우기
        if (type.startsWith('Sign')) {
            showSignAlert('우체국을 선택하세요', [
                { text: '부산신평2동 우체국', value: '부산신평2동' },
                { text: '부산사하우체국', value: '부산사하' }
            ]).then(postOffice => {
                if (!postOffice) return; // 선택하지 않으면 종료
                proceedPrinting(type, postOffice);
            });
        } else {
            // 봉투 출력이 아닌 경우 기존 로직 유지
            proceedPrinting(type);
        }
    }

    // 🖨️ 기존 로직 유지하면서, 봉투 출력 시 우체국 값 추가
    function proceedPrinting(type, postOffice = '') {
        let $subChkBatch = $('.subBatch:checked');
        let screenWidth = window.screen.width,
            screenHeight = window.screen.height,
            windowWidth = 930,
            windowHeight = 800,
            leftPosition = (screenWidth - windowWidth) / 2,
            topPosition = (screenHeight - windowHeight) / 2;
            
        /* 🏞️ 유광/무광 사진 출력 로직 기존 유지 */
        if(type == 'Image?isGloss=N' || type == 'Image?isGloss=Y') {
            let writeIdxArr = [];
            let totalGlossCnt = 0;
                
            for (let i = 0; i < $subChkBatch.length; i++) {
                let data = postList[$subChkBatch.eq(i).closest('tr').index()];
				// if (data.isLocked === 'Y' && data.state !== 'C') continue;

				if (!(data.isLocked === 'Y' && data.state !== 'C')) {
					if(type == 'Image?isGloss=Y') {
						totalGlossCnt += data.isGlossCnt;
					}else {
						totalGlossCnt += data.isNoneGlossCnt;
					}

					writeIdxArr.push($subChkBatch.eq(i).val());
				}
                // if(type == 'Image?isGloss=Y') {
                //     totalGlossCnt += data.isGlossCnt;
                // }else {
                //     totalGlossCnt += data.isNoneGlossCnt;
                // }

                // writeIdxArr.push($subChkBatch.eq(i).val());
                if(totalGlossCnt >= 100 || (i == ($subChkBatch.length - 1) && totalGlossCnt > 0)) {
                    let form = document.createElement("form");
                    form.setAttribute("method", "post");
                    form.setAttribute("action", `/admin/allPrinting${type}`);
                    form.setAttribute("target", "_blank");
                    
                    let hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
					hiddenField.setAttribute("name", "writeIdxArr");
                    hiddenField.setAttribute("value", JSON.stringify(writeIdxArr));
                    form.appendChild(hiddenField);
                    
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    
                    totalGlossCnt = 0;
                    writeIdxArr = [];                                                
                }
            }            
        } else {
			let writeIdxArr = [];
			for(let i=0; i<$subChkBatch.length; i++) {
				let idx = $subChkBatch.eq(i).val();
				let orderData = postList.find(item => item.idx == idx);

				if (!orderData) {
					showAlert("해당 데이터를 찾을 수 없습니다.");
					return;
				}

				// 🔒 락 걸린 항목은 제외
				if (orderData.isLocked === 'Y' && orderData.state !== 'C') continue;

				writeIdxArr.push(idx);
			}


            let form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", `/admin/allPrinting${type}`);
            form.setAttribute("target", "_blank");
                
            if(type == 'Post' && color == 'all') {
                showAlert("⚠️편지 일괄출력은 컬러를 선택해주세요");
                return;
            }
                
            let hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", "writeIdxArr");
            hiddenField.setAttribute("value", JSON.stringify(writeIdxArr));
            form.appendChild(hiddenField);

            hiddenField = document.createElement("input");        
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", "color");
            hiddenField.setAttribute("value", color);
            form.appendChild(hiddenField);

            // 🏣 봉투 출력인 경우 선택한 우체국 추가
            if (type.startsWith('Sign') && postOffice) {
                let postOfficeField = document.createElement("input");
                postOfficeField.setAttribute("type", "hidden");
                postOfficeField.setAttribute("name", "postOffice");
                postOfficeField.setAttribute("value", postOffice);
                form.appendChild(postOfficeField);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    }

//        // 일괄 출력 함수
//        function allPrinting(type) {
//            // 체크된 서브 항목들을 가져옴
//            let $subChkBatch = $('.subBatch:checked'),
//                writeIdxArr = [];
//
//            // 선택된 항목들의 idx를 배열에 추가
//            for (let i = 0; i < $subChkBatch.length; i++) {                        
//                writeIdxArr.push($subChkBatch.eq(i).val());
//            }
//
//            // 출력할 항목이 없거나 컬러를 선택하지 않은 경우 경고 메시지 출력
//            if(type == 'Post' && color == 'all') {
//                showAlert("⚠️편지 일괄출력은 컬러를 선택해주세요");
//                return;
//            }else if (!writeIdxArr.length) {
//                showAlert("출력하실 항목을 선택해주세요.");
//                return;
//            }
//
//            // 출력 창 크기 계산
//            let screenWidth = window.screen.width,
//                screenHeight = window.screen.height,ㄷ
//                windowWidth = 930,
//                windowHeight = 800,
//                leftPosition = (screenWidth - windowWidth) / 2,
//                topPosition = (screenHeight - windowHeight) / 2;
//
//            // 동적으로 폼 생성
//            let form = document.createElement("form");
//            form.setAttribute("method", "post");
//            form.setAttribute("action", `/admin/allPrinting${type}`);
//            form.setAttribute("target", "_blank");
//
//            // 선택된 항목들의 idx를 JSON 문자열로 변환하여 폼에 추가
//            let hiddenField = document.createElement("input");
//            hiddenField.setAttribute("type", "hidden");
//            hiddenField.setAttribute("name", "writeIdxArr");
//            hiddenField.setAttribute("value", JSON.stringify(writeIdxArr));
//            form.appendChild(hiddenField);
//
//            // 컬러 값을 폼에 추가
//            hiddenField = document.createElement("input");        
//            hiddenField.setAttribute("type", "hidden");
//            hiddenField.setAttribute("name", "color");
//            hiddenField.setAttribute("value", color);
//            form.appendChild(hiddenField);
//
//            // 동적으로 생성한 폼을 문서에 추가하고 폼 제출
//            document.body.appendChild(form);
//            form.submit();
//
//            // 폼 제출 후 폼 제거
//            document.body.removeChild(form);
//        }    
    
    // 주어진 URL에 대한 파일 크기를 구하는 함수
    // 'HEAD' 요청을 통해 메타데이터만 받아와서 Content-Length 헤더에서 파일 크기를 확인
    async function getFileSize(url) {
        try {
            const response = await fetch(url, { method: 'HEAD' }); // 메타데이터만 요청
            if (!response.ok) throw new Error(`Failed to fetch ${url}: ${response.statusText}`);

            const contentLength = response.headers.get("Content-Length"); // 파일 크기 가져오기
            return contentLength ? parseInt(contentLength, 10) : 0; // 파일 크기 반환
        } catch (error) {
            console.error(`Error getting file size for ${url}:`, error);
            return 0; // 오류 발생 시 0 반환
        }
    }
    
    // 파일 다운로드 및 병합을 처리하는 함수
    async function downFile(index) {
        const data = postList[index];  // 선택된 항목 데이터 가져오기
        const files = JSON.parse(data.pdfFiles);  // PDF 파일 목록 가져오기
        const basePath = '/assets/upload/files/';  // 파일 경로 기본값

        // 흑백(1) 또는 컬러(2) 처리
        for (let cnt = 1; cnt <= 2; cnt++) { /* 흑백, 컬러 */
            showLoadingBar();  // 로딩 바 표시
            // 각 파일을 순차적으로 처리
            for (let i = 0; i < files.length; i++) {

                let fileColor = data.fileColor.split("/")[i];  // 파일 색상 확인

                // 흑백(1) 또는 컬러(2)만 처리
                if (cnt == 1 && fileColor !== '블랙') continue;
                if (cnt == 2 && fileColor !== '컬러') continue;
				let downloadFileName = `(${cnt === 1 ? '블랙' : '컬러'})${data.orderId}(${i + 1}).pdf`; // 다운로드 파일명 설정

                const fileName = files[i].fileName;
                const pdfPath = basePath + fileName;  // PDF 파일 경로
                const fileUrl = basePath + fileName; //파일 Url

                try {
                    // PDF 파일 처리
                    if (fileName.endsWith('.pdf')) {
						console.log('pdf', fileName);
						await new Promise(resolve => setTimeout(resolve, 500));
						downloadFileDirectly(fileUrl, downloadFileName);  // 직접 다운로드 함수 호출
                    } 
                    // 이미지 파일 처리
                    else if (fileName.match(/\.(jpg|jpeg|png)$/)) {
						console.log('image', fileName);
                        const response = await fetch(pdfPath);
                        if (!response.ok) {
                            throw new Error(`Failed to fetch ${pdfPath}: ${response.statusText}`);
                        }

                        const imageBytes = await response.arrayBuffer();  // 이미지 파일의 배열 버퍼 가져오기
						let mergedPDF = await PDFDocument.create();  // 새로운 PDF 문서 생성
                        let image;
                        if (fileName.endsWith('.jpg') || fileName.endsWith('.jpeg')) {
                            image = await mergedPDF.embedJpg(imageBytes);  // JPG 파일 처리
                        } else if (fileName.endsWith('.png')) {
                            image = await mergedPDF.embedPng(imageBytes);  // PNG 파일 처리
                        }

						if (image) {
							// 이미지 크기에 맞는 페이지를 추가하고 이미지 삽입
							const page = mergedPDF.addPage([image.width, image.height]);
							page.drawImage(image, {
								x: 0,
								y: 0,
								width: image.width,
								height: image.height,
							});
						}

						await new Promise(resolve => setTimeout(resolve, 500));
						const pdfBytes = await mergedPDF.save({ updateFieldAppearances: false });  // 병합된 PDF 저장
						download(pdfBytes, downloadFileName, "application/pdf");
                    } else {
                        console.warn(`Unsupported file type: ${fileName}`);  // 지원하지 않는 파일 유형 경고
                    }


                } catch (error) {
                    console.error(`Error processing file ${fileName}:`, error);  // 파일 처리 오류                    
                    downloadFileDirectly(fileUrl, downloadFileName);  // 오류 발생 시 파일 직접 다운로드
                }
            }

            hideLoadingBar();  // 로딩 바 숨기기
        }
    }
    

    // 개별 파일을 직접 다운로드하는 함수
    function downloadFileDirectly(fileUrl, fileName) {
        const link = document.createElement("a");  // <a> 엘리먼트 동적 생성
        link.href = fileUrl;  // 다운로드할 파일의 URL 설정
        link.download = fileName;  // 다운로드할 파일 이름 설정
        document.body.appendChild(link);  // <a> 엘리먼트를 DOM에 추가
        link.click();  // 링크 클릭으로 다운로드 실행
        document.body.removeChild(link);  // 다운로드 후 <a> 엘리먼트를 DOM에서 제거
    }

    // 모든 파일 다운로드 버튼 클릭 처리
    // function allFileDown() {
    //     let $allFileBtn = $('.allFileBtn'); // 모든 파일 다운로드 버튼

    //     // 각 버튼을 클릭하여 다운로드 시작
    //     for (let i = 0; i < $allFileBtn.length; i++) {
    //         $allFileBtn.eq(i).click();
    //     }
    // }
    async function allFileDown() {
		let $allFileBtn = $('.subBatch:checked').closest('tr').find('.allFileBtn');

        for (let i = 0; i < $allFileBtn.length; i++) {
            let $btn = $allFileBtn.eq(i);
            let idx = $btn.data('idx');
            let orderData = postList.find(item => item.idx == idx);

            // 🔒 락이 걸려 있고 상태가 'C'가 아닐 경우 => 스킵
            if (orderData && orderData.isLocked === 'Y' && orderData.state !== 'C') {
                continue;
            }

            // 락이 안 걸려있으면 다운로드 실행
            $btn.click();
			await new Promise(resolve => setTimeout(resolve, 500));
        }
    }

    async function downLibrary(index) {
        console.log('downLibrary 실행됨:', index);
        const data = postList[index];  // 선택된 자료 데이터
        if (!data) {
            showAlert("자료 정보를 찾을 수 없습니다.");
            return;
        }
        const files = JSON.parse(data.libraryFiles);  // 파일 목록
        const basePath = '/assets/upload/';  // 자료실 파일 경로
        console.log("자료 파일 목록:", files);


        for (let cnt = 1; cnt <= 2; cnt++) { // 1 = 흑백, 2 = 컬러
            showLoadingBar();  // 로딩 바 표시

            let mergedPDF = await PDFDocument.create(),
                isFile = false,
                downloadFileName = `(${cnt === 1 ? '블랙' : '컬러'})${data.orderId}_자료.pdf`;
            

            for (let i = 0; i < files.length; i++) {
                let libraryFileColor = data.libraryFileColor.split("/")[i];
                libraryFileColor = libraryFileColor === 'black' ? '블랙' : libraryFileColor;
                libraryFileColor = libraryFileColor === 'color' ? '컬러' : libraryFileColor;

                console.log("libraryFileColor 전체:", data.libraryFileColor);
                console.log("현재 i:", i);
                console.log("분할된 libraryFileColor:", data.libraryFileColor.split("/"));
                console.log("선택된 libraryFileColor:", libraryFileColor);

                if (cnt === 1 && libraryFileColor !== '블랙') continue;
                if (cnt === 2 && libraryFileColor !== '컬러') continue;
                console.log("@@@@@@@@@@");

                const fileName = files[i].fileName;
                const pdfPath = basePath + fileName;
                const fileUrl = location.origin + pdfPath;
                const fileSize = await getFileSize(location.origin + pdfPath);

                console.log(pdfPath);
                if (fileSize >= 30000000 || (fileName.endsWith('.pdf') && files.length === 1)) {
                    console.log(`Direct download: ${fileName}`);
                    downloadFileDirectly(fileUrl, downloadFileName);
                    continue;
                }

                try {
                    if (fileName.endsWith('.pdf')) {
                        const response = await fetch(pdfPath);
                        if (!response.ok) throw new Error(`Failed to fetch ${pdfPath}: ${response.statusText}`);
                        const existingPdfBytes = await response.arrayBuffer();
                        const pdfDoc = await PDFDocument.load(existingPdfBytes, { ignoreEncryption: true });
                        const pageCount = pdfDoc.getPageCount();

                        for (let page = 0; page < pageCount; page++) {
                            try {
                                const [copiedPage] = await mergedPDF.copyPages(pdfDoc, [page]);
                                mergedPDF.addPage(copiedPage);
                            } catch (pageError) {
                                console.warn(`Skipping page ${page} in ${fileName}:`, pageError);
                            }
                        }
                    } else if (fileName.match(/\.(jpg|jpeg|png)$/)) {
                        const response = await fetch(pdfPath);
                        if (!response.ok) throw new Error(`Failed to fetch ${pdfPath}: ${response.statusText}`);
                        const imageBytes = await response.arrayBuffer();
                        let image;
                        if (fileName.endsWith('.jpg') || fileName.endsWith('.jpeg')) {
                            image = await mergedPDF.embedJpg(imageBytes);
                        } else if (fileName.endsWith('.png')) {
                            image = await mergedPDF.embedPng(imageBytes);
                        }
                        if (image) {
                            const page = mergedPDF.addPage([image.width, image.height]);
                            page.drawImage(image, { x: 0, y: 0, width: image.width, height: image.height });
                        }
                    } else {
                        console.warn(`Unsupported file type: ${fileName}`);
                    }

                    isFile = true;
                } catch (error) {
                    console.error(`Error processing file ${fileName}:`, error);
                    downloadLibraryDirectly(fileUrl, downloadFileName);
                }
            }

            if (isFile) {
                const pdfBytes = await mergedPDF.save({ updateFieldAppearances: false });
                download(pdfBytes, downloadFileName, "application/pdf");
            }

            hideLoadingBar(); // 로딩 바 숨기기
        }
    }

    // 개별 파일을 직접 다운로드하는 함수
    function downloadLibraryDirectly(fileUrl, fileName) {
        const link = document.createElement("a");  // <a> 엘리먼트 동적 생성
        link.href = fileUrl;  // 다운로드할 파일의 URL 설정
        link.download = fileName;  // 다운로드할 파일 이름 설정
        document.body.appendChild(link);  // <a> 엘리먼트를 DOM에 추가
        link.click();  // 링크 클릭으로 다운로드 실행
        document.body.removeChild(link);  // 다운로드 후 <a> 엘리먼트를 DOM에서 제거
    }

    function allLibraryDown() {
        let $allLibraryBtn = $('.allLibraryBtn'); // 모든 파일 다운로드 버튼

        for (let i = 0; i < $allLibraryBtn.length; i++) {
            let $btn = $allLibraryBtn.eq(i);
            let idx = $btn.data('idx');
            let orderData = postList.find(item => item.idx == idx);

            // 🔒 락이 걸려 있고 상태가 'C'가 아닐 경우 => 스킵
            if (orderData && orderData.isLocked === 'Y' && orderData.state !== 'C') {
                continue;
            }

            // 락이 안 걸려있으면 다운로드 실행
            $btn.click();
        }
    }
    

    function showSignAlert(message, options) {
        return new Promise((resolve) => {
            // 기존 alertBox와 overlay가 있으면 삭제
            let existingBox = document.getElementById('customAlertBox');
            let existingOverlay = document.getElementById('customOverlay');
            if (existingBox) document.body.removeChild(existingBox);
            if (existingOverlay) document.body.removeChild(existingOverlay);

            // 🔹 오버레이(배경을 어둡게 처리)
            let overlay = document.createElement('div');
            overlay.id = 'customOverlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.background = 'rgba(0, 0, 0, 0.5)'; // 반투명한 검은색
            overlay.style.zIndex = '999';
            overlay.style.cursor = 'pointer';

            overlay.addEventListener('click', function() {
                document.body.removeChild(alertBox);
                document.body.removeChild(overlay);
                resolve(null); // 아무 값도 반환하지 않음
            });


            // 팝업 컨테이너 생성
            let alertBox = document.createElement('div');
            alertBox.id = 'customAlertBox';
            alertBox.style.position = 'fixed';
            alertBox.style.top = '50%';
            alertBox.style.left = '50%';
            alertBox.style.transform = 'translate(-50%, -50%)';
            alertBox.style.background = 'white';
            alertBox.style.padding = '20px';
            alertBox.style.boxShadow = '0px 0px 10px rgba(0, 0, 0, 0.3)';
            alertBox.style.borderRadius = '8px';
            alertBox.style.textAlign = 'center';
            alertBox.style.zIndex = '1000';
            alertBox.style.width = '320px';

            // 메시지 추가
            let messageElem = document.createElement('p');
            messageElem.innerText = message;
            messageElem.style.marginBottom = '15px';
            messageElem.style.fontWeight = 'bold';
            messageElem.style.fontSize = '16px';
            alertBox.appendChild(messageElem);

            

            // 버튼 컨테이너 (세로 정렬)
            let btnContainer = document.createElement('div');
            btnContainer.style.display = 'flex';
            btnContainer.style.flexDirection = 'column'; // 세로 정렬
            btnContainer.style.alignItems = 'center'; // 중앙 정렬
            btnContainer.style.gap = '10px';

            // 옵션 버튼 추가
            options.forEach(option => {
                let btn = document.createElement('button');
                btn.innerText = option.text;
                btn.style.padding = '12px';
                btn.style.width = '90%';
                btn.style.border = '1px solid #007BFF';
                btn.style.borderRadius = '5px';
                btn.style.background = '#007BFF';
                btn.style.color = 'white';
                btn.style.fontSize = '16px';
                btn.style.cursor = 'pointer';

                btn.onclick = () => {
                    document.body.removeChild(alertBox);
                    document.body.removeChild(overlay); // 모달 닫으면 배경도 제거
                    resolve(option.value);
                };

                btnContainer.appendChild(btn);
            });

            alertBox.appendChild(btnContainer);

            // 문서에 추가
            document.body.appendChild(overlay);
            document.body.appendChild(alertBox);
        });
    }



    // 서명 파일을 다운로드하는 함수
    // writeIdx와 big 파라미터를 사용하여 서명 페이지를 새 창에서 열고 다운로드 완료 메시지를 기다림
    function downSign(writeIdx, big = 'N') {
        return new Promise((resolve, reject) => {
            // 📌 Step 1: 봉투 선택 먼저!
            showSignAlert('사용할 봉투를 선택하세요', [
                { text: '소봉투', value: 'N' },
                { text: '대봉투', value: 'Y' }
            ]).then(selectedBig => {
                if (selectedBig === null) return; // 사용자가 닫으면 중단

                // 📌 Step 2: 우체국 선택
                showSignAlert('우체국을 선택하세요', [
                    { text: '부산신평2동 우체국', value: '부산신평2동' },
                    { text: '부산사하우체국', value: '부산사하' }
                ]).then(postOffice => {
                    if (!postOffice) return;

                    let screenWidth = window.screen.width,
                        screenHeight = window.screen.height,
                        windowWidth = 800,
                        windowHeight = 800,
                        leftPosition = (screenWidth - windowWidth) / 2,
                        topPosition = (screenHeight - windowHeight) / 2;

                    let newWindow = window.open(
                        `/admin/signView/${writeIdx}?big=${selectedBig}&postOffice=${encodeURIComponent(postOffice)}`,
                        '_blank',
                        `width=${windowWidth},height=${windowHeight},top=${topPosition},left=${leftPosition}`
                    );

                    newWindow.focus();

                    window.addEventListener('message', function (event) {
                        if (event.origin !== window.location.origin) {
                            return;
                        }

                        if (event.data === 'downloadComplete') {
                            resolve();
                        }
                    }, { once: true });
                });
            });
        });
    }



    // 편지 파일을 다운로드하는 함수
    // 주어진 writeIdx로 편지 페이지를 새 창에서 열고 다운로드 완료 메시지를 기다림
    function downLetter(writeIdx){
        return new Promise((resolve, reject) => {
            let screenWidth = window.screen.width, // 화면 너비
                screenHeight = window.screen.height, // 화면 높이
                windowWidth = 800, // 새 창의 너비
                windowHeight = 800, // 새 창의 높이
                leftPosition = (screenWidth - windowWidth) / 2, // 화면 중앙에 창을 띄우기 위한 왼쪽 위치
                topPosition = (screenHeight - windowHeight) / 2; // 화면 중앙에 창을 띄우기 위한 위쪽 위치

            // 새 창 열기
            let newWindow = window.open('/admin/postView/' + writeIdx, '_blank', 'width=' + windowWidth + ',height=' + windowHeight + ',top=' + topPosition + ',left=' + leftPosition);

            newWindow.focus(); // 새 창에 포커스 주기

            // 메시지 이벤트 리스너 설정 (다운로드 완료 메시지를 기다림)
            window.addEventListener('message', function(event) {
                if (event.origin !== window.location.origin) {
                    return; // 다른 출처에서 온 메시지는 무시
                }

                if (event.data === 'downloadComplete') { // 다운로드가 완료되면
                    resolve(); // Promise를 해결
                }
            }, { once: true }); // 이벤트 리스너는 한 번만 실행됨    
        });
    }

    // 이미지 파일을 다운로드하는 함수
    // 주어진 writeIdx와 isGloss 파라미터를 사용하여 이미지 페이지를 새 창에서 열고 다운로드 완료 메시지를 기다림
    function downImg(writeIdx, isGloss){
        return new Promise((resolve, reject) => {
            let screenWidth = window.screen.width, // 화면 너비
                screenHeight = window.screen.height, // 화면 높이
                windowWidth = 800, // 새 창의 너비
                windowHeight = 800, // 새 창의 높이
                leftPosition = (screenWidth - windowWidth) / 2, // 화면 중앙에 창을 띄우기 위한 왼쪽 위치
                topPosition = (screenHeight - windowHeight) / 2; // 화면 중앙에 창을 띄우기 위한 위쪽 위치

            // 새 창 열기 (isGloss 파라미터에 따라 URL이 다르게 구성됨)
            let newWindow = window.open('/admin/postImgView/' + writeIdx + `?isGloss=${isGloss}`, '_blank', 'width=' + windowWidth + ',height=' + windowHeight + ',top=' + topPosition + ',left=' + leftPosition);

            newWindow.focus(); // 새 창에 포커스 주기

            // 메시지 이벤트 리스너 설정 (다운로드 완료 메시지를 기다림)
            window.addEventListener('message', function(event) {
                if (event.origin !== window.location.origin) {
                    return; // 다른 출처에서 온 메시지는 무시
                }

                if (event.data === 'downloadComplete') { // 다운로드가 완료되면
                    resolve(); // Promise를 해결
                }
            }, { once: true }); // 이벤트 리스너는 한 번만 실행됨  
        });
    }

    
    // 상태를 변경하는 함수
    async function changeState($this) {
        const writeIdx = $this.data('idx'); // 데이터 인덱스
        const newState = $this.val(); // 변경할 상태 값

        const orderData = postList.find(item => item.idx == writeIdx);
        if (!orderData) {
          showAlert("해당 데이터를 찾을 수 없습니다.");
          return;
        }

        let requestData = {
          writeIdx: writeIdx,
          state: newState,
        };

        if (newState === 'I') {
          requestData.prev_saveCount = orderData.isSaveCnt || 0;
        }
  
        // 서버로 상태 변경 요청을 보내고 응답 받기
        const changeStateRes = await postJson('/adminApi/changeState', requestData, false);

        // 응답이 실패한 경우 알림 표시
        if (!changeStateRes.result) {
            showAlert(changeStateRes.msg);
            return;
        }

        // 상태 변경 후 페이지 새로 고침
        location.reload();
    }

    // 검색 폼을 제출하는 함수
    function searchForm() {
        $('#searchForm').submit(); // 폼 제출
    }

    // 현재 날짜와 시간을 특정 형식으로 포맷팅하여 반환하는 함수
    function getFormattedDate() {
        const today = new Date();

        // 년, 월, 일, 시, 분, 초를 2자리로 포맷팅
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // 월은 0부터 시작하므로 +1
        const day = String(today.getDate()).padStart(2, '0');
        const hours = String(today.getHours()).padStart(2, '0');
        const minutes = String(today.getMinutes()).padStart(2, '0');
        const seconds = String(today.getSeconds()).padStart(2, '0');

        // 원하는 형식으로 포맷팅
        const formattedDate = `${year}${month}${day}${hours}${minutes}${seconds}`;

        return formattedDate;
    }

    function getCashReceipt() {
        const today = new Date();

        // 년, 월, 일, 시, 분, 초를 2자리로 포맷팅
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // 월은 0부터 시작하므로 +1
        const day = String(today.getDate()).padStart(2, '0');
        const hours = String(today.getHours()).padStart(2, '0');
        const minutes = String(today.getMinutes()).padStart(2, '0');
        const seconds = String(today.getSeconds()).padStart(2, '0');

        // 원하는 형식으로 포맷팅
        const formattedDate = `${year}${month}${day}`;

        return formattedDate;
    }

    // 문자열을 ArrayBuffer로 변환하는 함수
    function s2ab(s) {
        var buf = new ArrayBuffer(s.length); // 문자열을 ArrayBuffer로 변환
        var view = new Uint8Array(buf); // ArrayBuffer를 Uint8Array로 뷰 생성
        for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; // 문자열을 바이트로 변환
        return buf;
    }

    // 엑셀 다운로드 날짜를 설정하는 함수
    async function setExcelDownDate(writeIdx, registrationNumber) {
        // 사용자로부터 엑셀 다운로드 시간을 입력 받기
        let input = prompt("엑셀다운시간을 입력해주세요", registrationNumber);

        // 사용자가 입력을 취소한 경우
        if (input === null) {
            return;
        }

        // 서버에 엑셀 다운로드 시간을 설정 요청
        const setExcelDownDateRes = await postJson('/adminApi/setExcelDownDate', {
            writeIdx: writeIdx, // 데이터 인덱스
            excelDownDate: input // 입력된 엑셀 다운로드 시간
        }, false);

        // 설정 실패 시 알림
        if (!setExcelDownDateRes.result) {
            showAlert(setExcelDownDateRes.msg);
            return;
        }

        // 엑셀 다운로드 시간이 설정된 버튼 업데이트
        $(`.excelDownDate[data-idx="${writeIdx}"]`)
            .attr('onclick', `setExcelDownDate(${writeIdx}, ${input})`)
            .text(input);

        // 설정 성공 시 알림
        showAlert("엑셀다운시간이 입력되었습니다.");
    }

    // 등록 번호를 설정하는 함수
    async function setRegistrationNumber(writeIdx, registrationNumber) {
        // 사용자로부터 등록 번호를 입력 받기
        let input = prompt("등기번호를 입력해주세요", registrationNumber);

        // 사용자가 입력을 취소한 경우
        if (input === null) {
            return;
        }

        // 서버에 등록 번호 설정 요청
        const setRegistrationNumberRes = await postJson('/adminApi/setRegistrationNumber', {
            writeIdx: writeIdx, // 데이터 인덱스
            registrationNumber: input // 입력된 등록 번호
        }, false);

        // 설정 실패 시 알림
        if (!setRegistrationNumberRes.result) {
            showAlert(setRegistrationNumberRes.msg);
            return;
        }

        // 등록 번호가 설정된 버튼 업데이트
        $(`.registrationNumber[data-idx="${writeIdx}"]`)
            .attr('onclick', `setRegistrationNumber(${writeIdx}, ${input})`)
            .text(input);

        // 설정 성공 시 알림
        showAlert("등기번호가 입력되었습니다.");
    }

    // 등기 접수 번호 설정
    async function setRegistrationApplication(writeIdx, registrationApplication) {
      // 등록 접수 번호 받기
      let input = prompt("등기번호를 입력해주세요", registrationApplication);

      // 사용자가 입력을 취소한 경우
      if (input == null) {
        return;
      }

      // 서버에 등록 번호 설정 요청
      const setRegistrationNumberRes = await postJson('/adminApi/setRegistrationApplication', {
        writeIdx: writeIdx,
        registrationApplication: input
      }, false);
      
    }

    // 엑셀 파일 업로드를 위한 함수
    async function excelUpload() {
        // 서버로 엑셀 날짜 정보 요청
        const getExcelDateRes = await postJson('/adminApi/getExcelDate', {});

        // 엑셀 날짜 정보를 받지 못한 경우 알림
        if (!getExcelDateRes.result) {
            showAlert(getExcelDateRes.msg);
            return false;
        }

        // 받은 엑셀 파일 이름 표시
        $('#excelFileName1').text(getExcelDateRes.fileName[0]);
        $('#excelFileName2').text(getExcelDateRes.fileName[1]);
        $('#excelFileName3').text(getExcelDateRes.fileName[2]);

        // 엑셀 업로드 모달 창 표시
        $('#excelModal').modal('show');
    }

    
    // 엑셀 모달을 닫는 함수
    function closeExcelModal() {
        $('#excelModal').modal('hide'); // 엑셀 모달을 숨김
    }

    // 드래그 앤 드랍 이벤트 처리

    // 드래그 오버 이벤트: 드래그 중인 파일이 드롭 존에 들어왔을 때
    $('#dropZone').on('dragover', function (e) {
        e.preventDefault(); // 기본 동작 방지 (파일이 드래그되는 기본 동작을 막음)
        $(this).addClass('dragover'); // 드롭 존에 드래그된 파일을 나타내기 위해 스타일을 추가
    });

    // 드래그 리브 이벤트: 드래그한 파일이 드롭 존을 벗어났을 때
    $('#dropZone').on('dragleave', function () {
        $(this).removeClass('dragover'); // 드래그가 벗어난 후 드롭 존 스타일 제거
    });

    // 드롭 이벤트: 사용자가 파일을 드롭 했을 때
    $('#dropZone').on('drop', function (e) {
        e.preventDefault(); // 기본 동작 방지 (파일이 브라우저에 드롭되는 기본 동작을 막음)
        $(this).removeClass('dragover'); // 드롭 후 드롭 존 스타일 제거
        const files = e.originalEvent.dataTransfer.files; // 드래그한 파일들 가져오기
        if (files.length > 0) {
            readExcel(files[0]); // 첫 번째 파일을 읽는 함수 호출 (엑셀 파일)
        }
    });
    
    // 엑셀 파일을 읽고 서버로 전송하여 처리하는 함수
    async function readExcel(file = null) {                
        let fileInput = document.getElementById('fileToUpload'); // 파일 입력 요소 가져오기

        // 파일이 전달되지 않으면, 입력 필드에서 파일을 가져옴
        if(file == null) {
            file = fileInput.files[0];   
        }

        // 파일이 없다면 종료
        if(!file) {
            return;
        }

        let formData = new FormData(); // FormData 객체 생성

        // 엑셀 파일을 폼 데이터에 추가
        formData.append('excelFile', file);

        // postList에 있는 주문 ID들을 폼 데이터에 추가
        postList.forEach((value, index) => {
            formData.append(`orderIdArr[]`, value['orderId']);
        });

        // 엑셀 파일을 서버로 전송
        const readExcelRes = await postFormJson('/adminApi/readRegistrationNumber', formData);

        // 서버 응답에 실패하면 알림을 띄우고, 파일 입력 필드를 초기화
        if (!readExcelRes.result) {
            fileInput.value = '';
            showAlert(readExcelRes.msg);
            return false;
        }

        // 성공적인 처리 결과 알림
        alert(`총 ${readExcelRes.totalCnt}개 중, 성공:${readExcelRes.successCnt}개, 실패:${readExcelRes.failCnt}개 처리되었습니다.`);
        location.reload(); // 페이지 새로고침
        fileInput.value = ''; // 파일 입력 필드 초기화
    }

    // 배치 파일 업로드 버튼 클릭을 트리거하는 함수
    function allBatchSubmit() {
        $('#batchFileToUpload').click();   // 배치 파일 입력 버튼 클릭
    }

    // 배치 엑셀 파일을 읽고 서버로 전송하여 처리하는 함수
    async function readBatchExcel() {      
        let fileInput = document.getElementById('batchFileToUpload'), // 배치 파일 입력 요소
            file = fileInput.files[0]; // 선택된 파일

        // 파일이 없으면 종료
        if(!file) {
            return;
        }

        let formData = new FormData();  // FormData 객체 생성
        formData.append('excelFile', file); // 배치 엑셀 파일을 폼 데이터에 추가

        // 배치 엑셀 파일을 서버로 전송
        const readBatchExcelRes = await postFormJson('/adminApi/readBatchRegistrationNumber', formData);

        // 서버 응답에 실패하면 알림을 띄우고, 파일 입력 필드를 초기화
        if (!readBatchExcelRes.result) {
            fileInput.value = '';
            showAlert(readBatchExcelRes.msg);
            return false;
        }

        // 성공적인 처리 결과 알림
        alert(`총 ${readBatchExcelRes.totalCnt}개 중, 성공:${readBatchExcelRes.successCnt}개, 실패:${readBatchExcelRes.failCnt}개 처리되었습니다.`);
        location.reload(); // 페이지 새로고침
    }

    // 전화번호가 '010'으로 시작하지 않으면, 기본 번호를 반환하는 함수
    function checkNotPhone(senderTel, phoneNumber) {
        // 폰 번호가 없거나 '010'으로 시작하지 않는 경우
        if (!phoneNumber || !phoneNumber.startsWith('010')) {
            return formatPhoneNumber(senderTel); // 기본 전화번호 형식으로 포맷팅
        }

        // '010'으로 시작하는 번호라면, 해당 전화번호 포맷팅
        return formatPhoneNumber(phoneNumber);
    }

    
    // 엑셀 파일을 생성하고 다운로드하는 함수
    async function exportExcel() { 
        let jsonData = [],  // 엑셀에 들어갈 데이터 배열
            excelDownDate = getFormattedDate(), // 엑셀 다운로드 날짜
            stampName = $('input[name="stamp"]:checked').data('name'), // 우편 종류 (예: '준 등기우편')
            stateName = $('input[name="state"]:checked').data('name'), // 상태 (예: '배송 완료')
            isDetailExcel = ((pageState == 'R' || pageState == 'I' || pageState == 'S') && (stampName == '준 등기우편' || stampName == '등기우편' || stampName == '익일특급')); // 인쇄중 상태에서 상세 엑셀을 요청할 경우
            console.log(excelDownDate);

        let target = postList.filter(data => !(data.isLocked === 'Y' && data.state !== 'C'));

        // let target = postList; // 처리할 데이터 리스트
        console.log("전체 데이터 개수:", postList.length);


        // 엑셀 파일명 설정
        if(pageState == 'I' || pageState == 'S' || pageState == 'R') {
            fileName = excelDownDate + '_' + stampName + '.xlsx'; // 인쇄중 탭일 경우
        } else {
            fileName = excelDownDate + '_' + stateName + '.xlsx'; // 다른 탭일 경우
        }

        if(isDetailExcel) {          
            // 상세 엑셀 처리
            let writeIdxArr = [];

            postList.forEach((value, index) => {
                writeIdxArr.push(value['idx']); // 데이터의 idx 값을 배열에 추가
            });

            // 서버로 엑셀 데이터를 요청
            const exportExcelRes = await postJson('/adminApi/exportExcel', {
                writeIdxArr: writeIdxArr,
                stampName: stampName,
                excelDownDate: excelDownDate,
                fileName: fileName
            });

            console.log(exportExcelRes);

            // 서버 응답이 실패하면 알림 표시
            if (!exportExcelRes.result) {
                showAlert(exportExcelRes.msg);
                return false;
            }

            // 엑셀 다운로드 날짜 정보 업데이트
            for(let i=0; i<target.length; i++) {
                target[i]['excelDownDate'] = exportExcelRes.excelDownDate[i];
            }

        } else if(pageState != 'W' && pageState != 'R' && pageState != 'I' && pageState != 'S') {
            // 인쇄중 또는 다른 특정 상태가 아닐 경우 엑셀 파일 목록을 가져옴
            const queryString = window.location.search + '&isExcel=Y';
            const exportExcelRes = await postJson('/admin/post' + queryString, {});
            target = exportExcelRes.list; // 서버로부터 받은 데이터 리스트
        }

        // 엑셀 데이터 포맷을 생성
        for (let i = 0; i < target.length; i++) {
            let data = target[i];
            let letterPrice = stamp[data['stamp']]['name'] == '일반우편' ? `${comma(data['letterPrice'])}원` : `${data['productName']} * ${data['totalLetterCnt']}장 / ${comma(data['letterPrice'])}원`;         
            let obj = {};

            // 상세 엑셀 데이터 포맷 설정
            obj = {                    
                '받는 분': data['receiverName'],
                '우편번호': extractAddressInfo(data['receiverAddr'])['postCode'],
                '주소(시도+시군구+도로명+건물번호)': extractAddressInfo(data['receiverAddr'])['address'],
                '상세주소(동, 호수, 洞명칭, 아파트, 건물명 등)': data['receiverAddrDetail'],
                '일반전화(02-1234-5678)': '',
                '휴대전화(010-1234-5678)': checkNotPhone(data['senderTel'], data['receiverTel']),
                '등기번호(선납소포라벨만 입력가능)': '',
                '중량(g)' : ''
            };

            jsonData.push(obj); // jsonData 배열에 객체 추가
        }

        // 엑셀 파일 생성 및 설정
        let excelHandler = {
            getExcelFileName: function() {
                return fileName; // 엑셀 파일명 반환
            },
            getSheetName: function() {
                return '주문내역'; // 시트 이름 설정
            },
            getExcelData: function() {
                return jsonData; // 엑셀에 들어갈 데이터 반환
            },
            getWorksheet: function() {
                return XLSX.utils.json_to_sheet(this.getExcelData()); // JSON 데이터를 엑셀 시트로 변환
            }
        }

        // 워크북 생성
        var wb = XLSX.utils.book_new();

        // 워크시트 생성
        var newWorksheet = excelHandler.getWorksheet();

        // 엑셀 시트의 각 열 너비 계산
        const maxLengths = {};
        jsonData.forEach(row => {
            Object.keys(row).forEach(key => {
                const value = row[key].toString();
                if (!maxLengths[key]) {
                    maxLengths[key] = key.length; // 헤더 길이로 초기화
                }
                if (value.length > maxLengths[key]) {
                    maxLengths[key] = value.length;
                }                
            });
        });

        // 각 열의 너비를 설정
        newWorksheet['!cols'] = Object.keys(jsonData[0]).map(key => {
            return { wch: maxLengths[key] * 1.7 };
        });

        // 특정 열에 대해 텍스트 형식 적용
        const textFormatCols = ['주문자 전화번호', '받는사람 우편번호', '받는사람 전화번호', '엑셀다운시간'];
        jsonData.forEach((row, rowIndex) => {
            textFormatCols.forEach(col => {
                const cellRef = XLSX.utils.encode_cell({ r: rowIndex + 1, c: Object.keys(row).indexOf(col) });
                if (newWorksheet[cellRef]) {
                    newWorksheet[cellRef].z = '@'; // 텍스트 형식 적용
                }
            });
        });

        // 워크북에 워크시트 추가
        XLSX.utils.book_append_sheet(wb, newWorksheet, excelHandler.getSheetName());

        // 워크북을 엑셀 파일로 변환
        var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

        // Blob을 생성하여 파일로 저장
        saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), excelHandler.getExcelFileName());

        // 상세 엑셀 다운로드 후 알림 및 페이지 새로고침
        if(isDetailExcel) {
            showAlert('저장되었습니다')
            .then(() => {
                location.reload();   
            });
        }
    }
    
    // 주소 상세 모달을 열거나 닫는 함수
    function openAddrDetailModal(type, addrType = '') {                        
        let $addrModal = $('#addrDetailModal');  // 주소 상세 모달 객체 선택

        if(type == 'show'){  // 모달을 열 때
            // '선택' 옵션과 주소 유형에 맞는 주소를 보여주기 위해 HTML 설정
            let postSelectHtml = "<option value=''>선택</option>";
            let adrType = addressType[addrType];  // 주소 유형에 맞는 주소 데이터

            // 주소 유형에 맞는 항목들을 옵션으로 추가
            for(let adr of adrType){                
                postSelectHtml += `<option value="${adr}">${adr}</option>`;
            }

            // 주소 모달의 타이틀을 설정 ('군대 훈련소' 또는 '구치소/교도소/소년원')
            $('#addrTitle').html(addrType == 'army' ? '군대 훈련소' : '구치소/교도소/소년원');

            // 주소 선택 옵션을 설정하고, 선택 시 'changeAddrDetailSelect' 함수 호출
            $('#postSelect').html(postSelectHtml).attr('onchange', `changeAddrDetailSelect('${addrType}')`);

            // 주소 리스트를 초기화
            $("#addrList").html('<p class="empty" style="text-align:center">검색하실 항목을 선택해주세요.</p>');

            // 주소 상세 모달을 보여줌
            $addrModal.addClass('show');
        } else {  // 모달을 닫을 때
            $addrModal.removeClass('show');
        }
    }

    // 선택된 주소 유형에 맞는 세부 주소 목록을 업데이트하는 함수
    function changeAddrDetailSelect(adrType) {
        let list = '';  // 주소 목록을 담을 변수
        let adrList = addressList[adrType],  // 주소 목록 데이터
            choiceType = $('#postSelect option:selected').val();  // 선택된 옵션 값

        // 주소 목록에서 선택된 주소 유형에 해당하는 항목만 필터링하여 리스트 생성
        for(let i = 0; i < adrList.length; i++){
            let data = adrList[i],
                addressDetail = !data.addressDetail ? data.name : data.addressDetail;  // 상세 주소가 없으면 이름 사용

            if(data.region != choiceType) continue;  // 선택된 지역에 맞는 주소만 표시

            // 주소 정보와 '선택' 버튼을 포함한 HTML을 리스트에 추가
            list += `<div class="postBox">
                        <p class="postHead"><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i> ${data.name}</p>
                        <p class="postContent">주소 - (${data.post})${data.address}</p>
                        <p class="postContent">상세주소 - ${addressDetail}</p>
                        <button class="postChoiceBtn" onclick="choiceAddrDetail('${data.post}', '${data.address}', '${addressDetail}')">선택</button>
                    </div>`;
        }

        // 생성된 주소 리스트를 모달 안에 표시
        $('#addrList').html(list);
    }

    // 선택된 주소를 입력 필드에 반영하는 함수
    function choiceAddrDetail(post, address, addressDetail){        
        // 선택된 주소와 상세 주소를 입력 필드에 설정
        $("#addr").val(`(${post})${address}`);
        $("#addrDetail").val(addressDetail);            

        // 주소 상세 모달을 닫음
        openAddrDetailModal('hide');
    }
    
    /* 현금영수증 정보 다운로드 */
    function downCashReceipt(index) {
        let data = postList[index];

        console.log(data);

        // 입금이 확인되지 않은 경우 경고 메시지 표시 후 함수 종료
        if (data.isPay === 'N') {
            showAlert('입금이 확인되지 않은 주문입니다. 현금영수증을 다운로드할 수 없습니다.');
            return;
        }

        let regDate = data.regDate;
        let paymentDate = "20" + regDate.split(" ")[0].replace(/-/g, '');

        // 워크북 생성
        var wb = XLSX.utils.book_new();

        let jsonData = [
            ["엑셀 업로드 양식(현금영수증)", "", "", "", "", "", "", ""],
            [
                "○ 현재 시트에 발급할 내용을 입력하시기 바랍니다.('메모'는 선택 입력 사항이며 나머지 5개 항목은 모두 빠짐없이 작성)",
                "> 실제 업로드할 내용은 7행부터 입력하여야 하고, 최대 1,000건까지 입력 가능합니다.",
                "> 6개 항목 작성 시 [항목설명]시트를 참고하시고, 총거래금액/공급가액/부가가치세는 [부가가치세 계산식]시트를 이용해 복사하시면 편리합니다.",
                "> 총거래금액/공급가액/부가가치세 항목에 엑셀 함수(수식)가 포함되면 오류가 발생하므로 0 이상의 숫자와 ,(쉼표)만 입력하여야 합니다.",
                "> 발급할 내용 입력시 [올바른 예시], [잘못된 예시]를 참고하시기 바랍니다.",
                "> 일괄 발급 시 오류가 나오는 경우 [검증결과 오류코드 설명]시트를 참고하시어 오류 항목을 수정하시기 바랍니다.",
                "> 임의로 행을 추가/삭제 하시면 오류가 발생할 수 있습니다.",
                ""
            ],
            ["○ 고객요청 없이 자진발급하는 경우에는 발급수단번호에 010-000-1234로 입력하고, 용도구분은 0(소득공제용)으로 입력하여야 합니다."],
            [
                "○ 현재 시트에 내용 입력이 완료되면, [항목설명] 등 다른 시트를 삭제(수정)하지 말고 그대로 업로드하여 [파일검증], [일괄발급] 바랍니다.",
                "> 입력하는 각 항목의 셀 서식은 텍스트, 숫자 모두 가능하며, 엑셀 파일 확장자는 XLS, XLSX 모두 가능합니다."
            ],
            ["○ 현금영수증 일괄 발급에 어려움이 있으신 경우 국세상담센터(국번없이 126 → 1번 → 1번)로 문의주시기 바랍니다."],
            ["용도구분", "발급수단번호", "총거래금액(합계)", "공급가액", "부가가치세", "메모", "", ""],
            [
                data.cashReceiptType === 'earnings' ? '0' : '1', // 용도구분
                data.cashReceiptNumber.toString(), // 발급수단번호
                data.realTotalPrice, // 총거래금액
                data.realTotalPrice, // 공급가액
                0, // 부가가치세
                `${data.productName} - ${data.cashReceiptEmail}` // 메모
            ]
        ];

        // 워크시트 생성
        var newWorksheet = XLSX.utils.aoa_to_sheet(jsonData);

        // 행 높이 설정
        newWorksheet['!rows'] = [
            { hpx: 30 },  // 첫 번째 행 높이
            { hpx: 137 }, // 설명 행
            { hpx: 20 },  // 고객 요청 관련
            { hpx: 20 },
            { hpx: 20 },
            { hpx: 20 },
            { hpx: 20 }
        ];

        // 열 너비 설정
        newWorksheet['!cols'] = [
            { wch: 20 }, // 용도구분
            { wch: 30 }, // 발급수단번호
            { wch: 20 }, // 총거래금액
            { wch: 20 }, // 공급가액
            { wch: 15 }, // 부가가치세
            { wch: 40 }  // 메모
        ];

        // 발급수단번호를 텍스트 형식으로 유지
        for (let cell in newWorksheet) {
            if (cell.startsWith('B')) {
                newWorksheet[cell].z = '@'; // 텍스트 형식 유지
            }
        }

        // 워크북에 워크시트 추가
        XLSX.utils.book_append_sheet(wb, newWorksheet, '현금영수증');

        // 워크북을 엑셀 파일로 변환
        var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

        // Blob을 생성하여 파일로 저장
        saveAs(new Blob([s2ab(wbout)], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }), `${paymentDate}.xlsx`);
    }

    // Blob을 위한 binary 변환 함수
    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) {
            view[i] = s.charCodeAt(i) & 0xFF;
        }
        return buf;
    }


    async function downloadAllCashReceipts() {
        const queryString = window.location.search + '&isExcel=Y';
        try {
            const response = await fetch('/admin/post' + queryString);
            const data = await response.json();

            let validReceipts = data.list.filter(item => item.isCashReceipt === 'Y' && item.isPay === 'Y' && !['T', 'B', 'C'].includes(item.state) && !(item.isLocked === 'Y' && item.state !== 'C'));

            if (validReceipts.length === 0) {
                showAlert("다운로드할 현금영수증이 없습니다.");
                return;
            }

            // 날짜 형식으로 파일명 생성
            let today = new Date();
            let fileDate = today.getFullYear().toString() + 
                        String(today.getMonth() + 1).padStart(2, '0') + 
                        String(today.getDate()).padStart(2, '0');

            let jsonData = [
                ["엑셀 업로드 양식(현금영수증)", "", "", "", "", "", "", ""],
                [
                    "○ 현재 시트에 발급할 내용을 입력하시기 바랍니다.('메모'는 선택 입력 사항이며 나머지 5개 항목은 모두 빠짐없이 작성)",
                    "> 실제 업로드할 내용은 7행부터 입력하여야 하고, 최대 1,000건까지 입력 가능합니다.",
                    "> 6개 항목 작성 시 [항목설명]시트를 참고하시고, 총거래금액/공급가액/부가가치세는 [부가가치세 계산식]시트를 이용해 복사하시면 편리합니다.",
                    "> 총거래금액/공급가액/부가가치세 항목에 엑셀 함수(수식)가 포함되면 오류가 발생하므로 0 이상의 숫자와 ,(쉼표)만 입력하여야 합니다.",
                    "> 발급할 내용 입력시 [올바른 예시], [잘못된 예시]를 참고하시기 바랍니다.",
                    "> 일괄 발급 시 오류가 나오는 경우 [검증결과 오류코드 설명]시트를 참고하시어 오류 항목을 수정하시기 바랍니다.",
                    "> 임의로 행을 추가/삭제 하시면 오류가 발생할 수 있습니다.",
                    ""
                ],
                ["○ 고객요청 없이 자진발급하는 경우에는 발급수단번호에 010-000-1234로 입력하고, 용도구분은 0(소득공제용)으로 입력하여야 합니다."],
                [
                    "○ 현재 시트에 내용 입력이 완료되면, [항목설명] 등 다른 시트를 삭제(수정)하지 말고 그대로 업로드하여 [파일검증], [일괄발급] 바랍니다.",
                    "> 입력하는 각 항목의 셀 서식은 텍스트, 숫자 모두 가능하며, 엑셀 파일 확장자는 XLS, XLSX 모두 가능합니다."
                ],
                ["○ 현금영수증 일괄 발급에 어려움이 있으신 경우 국세상담센터(국번없이 126 → 1번 → 1번)로 문의주시기 바랍니다."],
                ["용도구분", "발급수단번호", "총거래금액(합계)", "공급가액", "부가가치세", "메모", "", ""]
            ];

            validReceipts.forEach((item) => {
                jsonData.push([
                    item.cashReceiptType === 'earnings' ? '0' : '1', // 용도구분
                    item.cashReceiptNumber.toString(), // 발급수단번호 (숫자 + "-" 형식 유지)
                    item.realTotalPrice, // 총거래금액
                    item.realTotalPrice, // 공급가액
                    0, // 부가가치세
                    `${item.productName} - ${item.cashReceiptEmail}` // 메모
                ]);
            });

            // 워크북 생성
            let wb = XLSX.utils.book_new();
            let newWorksheet = XLSX.utils.aoa_to_sheet(jsonData);

            // 행 높이 설정
            newWorksheet['!rows'] = [
                { hpx: 30 },  // 첫 번째 행 높이
                { hpx: 137 }, // 설명 행
                { hpx: 20 },  // 고객 요청 관련
                { hpx: 20 },
                { hpx: 20 },
                { hpx: 20 },
                { hpx: 20 }
            ];

            // 열 너비 설정
            newWorksheet['!cols'] = [
                { wch: 20 }, // 용도구분
                { wch: 30 }, // 발급수단번호
                { wch: 20 }, // 총거래금액
                { wch: 20 }, // 공급가액
                { wch: 15 }, // 부가가치세
                { wch: 40 }  // 메모
            ];

            // 발급수단번호를 텍스트 형식으로 유지
            for (let cell in newWorksheet) {
                if (cell.startsWith('B')) {
                    newWorksheet[cell].z = '@'; // 텍스트 형식 유지
                }
            }

            // 워크북에 워크시트 추가
            XLSX.utils.book_append_sheet(wb, newWorksheet, '현금영수증 목록');

            // 워크북을 엑셀 파일로 변환
            let wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

            // Blob을 생성하여 파일 저장 (YYYYMMDD.xlsx 형식)
            saveAs(new Blob([s2ab(wbout)], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }), `${fileDate}.xlsx`);

        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    // Blob을 위한 binary 변환 함수
    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) {
            view[i] = s.charCodeAt(i) & 0xFF;
        }
        return buf;
    }



    function openModal(modalId) {
        if (modalId === 'orderSummaryModal') {
            const queryString = window.location.search + '&isExcel=Y';

            // `finishStartDate`와 `finishEndDate`를 GET 파라미터에서 가져오기
            let urlParams = new URLSearchParams(window.location.search);
            let finishDateState = urlParams.get("finishDateState"); // 발송일자 라디오 버튼 값
            let today = '<?=date("Y-m-d")?>'; // 오늘 날짜 가져오기

            // 만약 finishDateState(발송일자 선택 값)이 없다면 기본값을 오늘 날짜로 설정
            let finishStartDate = (finishDateState === 'CHOICE') ? urlParams.get("finishStartDate") : today;
            let finishEndDate = (finishDateState === 'CHOICE') ? urlParams.get("finishEndDate") : today;

            fetch(`/admin/postToday?isExcel=Y&finishStartDate=${finishStartDate}&finishEndDate=${finishEndDate}`)
                .then(response => response.json())
                .then(data => {
                    const postList = data.list; // 전체 데이터를 가져옴

                    const stampCounts = {};
                    const envelopeCounts = { 'normal': 0, 'big': 0 };
                    const colorCounts = {};

                    const categoryCounts = {
                        'black': 0, 'kraft': 0, 'tema': 0, 'purple': 0, 'green': 0, 'ivory': 0, 'yellow': 0, 
                        'blue': 0, 'pink': 0, 'sdoku': 0, 'difference': 0, 'glossyPhoto': 0, 
                        'mattePhoto': 0, 'blackDocument': 0, 'colorDocument': 0,'smallEnvelope': 0, 'bigEnvelope': 0
                    };

                    postList.forEach(item => {
                        let colorKey = getColorKeyFromLetterIdx(item.letterIdx, item.cateIdx, item.name);
                        if (categoryCounts[colorKey] !== undefined) {
                            categoryCounts[colorKey] += parseInt(item.totalLetterCnt);
                        }

                        // 📌 **유광 & 무광 사진 카운팅 추가**
                        if (item.isGlossCnt > 0) {
                            categoryCounts['glossyPhoto'] += item.isGlossCnt;
                        }
                        if (item.isNoneGlossCnt > 0) {
                            categoryCounts['mattePhoto'] += item.isNoneGlossCnt;
                        }

                        if (item.fileColor && item.pdfFiles) {
                            const colors = item.fileColor.split('/');
                            let pdfFiles = [];

                            if (typeof item.pdfFiles === 'string') {
                                try {
                                    pdfFiles = JSON.parse(item.pdfFiles);
                                } catch (e) {
                                    pdfFiles = [];
                                }
                            } else if (Array.isArray(item.pdfFiles)) {
                                pdfFiles = item.pdfFiles;
                            }

                            for (let i = 0; i < colors.length; i++) {
                                const color = colors[i].trim();
                                const pageCount = parseInt(pdfFiles[i]?.pageCount || 0);

                                if (color === '블랙') {
                                    categoryCounts['blackDocument'] += pageCount;
                                } else if (color === '컬러') {
                                    categoryCounts['colorDocument'] += pageCount;
                                }
                            }
                        }

                        const isBigEnvelope = (
                            (parseInt(item.totalLetterCnt) + parseInt(item.totalPhotoCnt) >= 30) ||
                            item.totalPdfFileCnt > 0 ||
                            item.totalLibraryFileCnt > 0
                        );

                        // 📌 **봉투 카운팅 추가**
                        if (isBigEnvelope) {
                            console.log('[대봉투]', {
                                name: item.name,
                                totalLetterCnt: item.totalLetterCnt,
                                totalPhotoCnt: item.totalPhotoCnt,
                                totalPdfFileCnt: item.totalPdfFileCnt,
                                totalLibraryFileCnt: item.totalLibraryFileCnt
                            });

                            categoryCounts['bigEnvelope'] += 1;
                        } else {
                            categoryCounts['smallEnvelope'] += 1;
                        }
                    });

                    // document.getElementById('totalStampCount').innerText = Object.values(categoryCounts).reduce((acc, count) => acc + count, 0);
                    updateSummaryTable(categoryCounts);
                })
                .catch(error => console.error('Error fetching data:', error));
        }
        $('#' + modalId).modal('show');
    }


    function getColorKeyFromLetterIdx(letterIdx, cateIdx, name) {
        switch (letterIdx) {
            case '134': return 'purple'; // 보라색
            case '133': return 'green';  // 초록색
            case '132': return 'ivory';  // 아이보리색
            case '35': return 'yellow';  // 노란색
            case '34': return 'blue';    // 파란색
            case '33': return 'pink';    // 분홍색
        }

        // cateIdx 기반 조건 추가
        if (cateIdx === '1') {
            if (name.includes('크라프트')) {
                return 'kraft'; // 크라프트
            }
            return 'black'; // 기본 블랙
        }


        if (cateIdx > 3 && cateIdx != 38) {
            return 'tema'; // 특정 카테고리(테마)
        }

        if (cateIdx == 38) {
            if (name.includes('스도쿠')) {
                return 'sdoku'; // 스도쿠 관련 항목
            } else if (name.includes('숨은그림찾기')) {
                return 'difference'; // 숨은그림찾기 관련 항목
            }
        }

        return 'unknown';
    }


    function updateSummaryTable(countObj) {
        let headerRow = document.getElementById('summaryHeaderRow');
        let dataRow = document.getElementById('summaryDataRow');

        if (!headerRow || !dataRow) {
            console.error("Element not found for IDs:", 'summaryHeaderRow', 'summaryDataRow');
            return;
        }

        headerRow.innerHTML = '<th>종류</th>';
        dataRow.innerHTML = '<th>수량</th>';

        const categoryNames = {
            'black': '블랙', 'kraft': '크라프트', 'tema': '테마', 'purple': '연보라', 'green': '그린', 'ivory': '아이보리',
            'yellow': '옐로우', 'blue': '블루', 'pink': '핑크', 'sdoku': '스도쿠', 
            'difference': '숨은그림', 'glossyPhoto': '유광사진', 'mattePhoto': '무광사진',
            'blackDocument': '흑백문서', 'colorDocument': '컬러문서',
            'smallEnvelope': '소봉투', 'bigEnvelope': '대봉투'
        };

        for (const [key, name] of Object.entries(categoryNames)) {
            headerRow.innerHTML += `<th>${name}</th>`;
            dataRow.innerHTML += `<td>${countObj[key] || 0}장</td>`;
        }
    }

    function closeModal(modalId) {
        $('#' + modalId).modal('hide');
    }

    async function lockSelectedOrders() {
        let $subChkBatch = $('.subBatch:checked');
        if ($subChkBatch.length === 0) {
            showAlert("락을 걸 항목을 선택해주세요.");
            return;
        }

        if (!confirm("선택된 주문에 상태 변경 제한(락)을 거시겠습니까?")) return;

        let writeIdxArr = [];

        $subChkBatch.each(function () {
            writeIdxArr.push($(this).val());
        });

        const res = await postJson('/adminApi/lockOrders', {
            writeIdxArr: writeIdxArr
        });

        if (!res.result) {
            showAlert(res.msg);
            return;
        }

        showAlert("✅ 선택된 주문에 락이 설정되었습니다.").then(() => location.reload());
    }

    async function unlockSelectedOrders() {
        let $subChkBatch = $('.subBatch:checked');
        if ($subChkBatch.length === 0) {
            showAlert("락을 해제할 항목을 선택해주세요.");
            return;
        }

        if (!confirm("선택된 주문의 락을 해제하시겠습니까?")) return;

        let writeIdxArr = [];

        $subChkBatch.each(function () {
            writeIdxArr.push($(this).val());
        });

        const res = await postJson('/adminApi/unlockOrders', {
            writeIdxArr: writeIdxArr
        });

        if (!res.result) {
            showAlert(res.msg);
            return;
        }

        showAlert("🔓 선택된 주문의 락이 해제되었습니다.").then(() => location.reload());
    }



</script>

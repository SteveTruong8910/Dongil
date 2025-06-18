<style>
    #notice table td{ font-size: 13px; }
    #container{  overflow-x: auto; }
    table { width: 2300px !important; }
    .chkBtn{ display: block; margin-top: -1px; color: #4444f7; border-radius: 5px; padding: 3px 6px;  cursor: unset; }

</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>


<p class="title">사서함 관리</p>

<div id="notice">
    <div class="local">
        총 사서함 수 <?=count($list)?>건
    </div>

    <form id="searchForm" method="get">
        <div class="filter">
            <input type="hidden" name="page" value="1"/>
            <select name="searchType" class="searchType">
                <option value="senderName" <?=$searchData['searchType'] == 'senderName' ? 'selected' : ''?>>회원명</option>
                <option value="senderTel" <?=$searchData['searchType'] == 'senderTel' ? 'selected' : ''?>>회원전화번호</option>
                <option value="idx" <?=$searchData['searchType'] == 'idx' ? 'selected' : ''?>>회원번호</option>
            </select>
            <input type="text" name="searchTxt" class="searchTxt" value="<?=$searchData['searchTxt']?>" placeholder="검색어 입력"/>
            <button type="submit" class="btnDownload">검색</button>
            <button type="button" class="btnDownload" onclick="openRegisterModal()">📬 사서함 등록</button>


        </div>
    </form>

    <table class="summary-table" style="margin-top: 10px;">
        <thead>
            <tr>
                <th width="50">번호</th>
                <th>회원명</th>
                <th>회원번호</th>
                <th>상태</th>
                <th width="200">선택</th>
                <th width="250">보낸 사람</th>
                <!-- <th>받는 사람</th> -->
                <th>등록일</th>
                <th>스캔파일</th>
                <th>스캔 결제상태</th>
                <th>스캔 결제수단</th>
                <th>택배 결제상태</th>
                <th>택배 결제수단</th>
                <th>택배신청</th>
                <th>수령자 이름</th>
                <th width="220">수령자 주소</th>
                <th width="200">상세주소</th>
                <th>전화번호</th>
                <th width="200">운송장번호</th>
                <!-- <th>관리</th> -->
            </tr>
        </thead>
        <tbody>
            <? if(!count($list)) { ?>
                <tr><td colspan="10" class="empty">등록된 사서함 내역이 없습니다.</td></tr>
            <? } ?>
            <? foreach($list as $row) { ?>
                <tr>
                    <td><?=$row['mailboxIdx']?></td>
                    <td><?=$row['nickname']?></td>
                    <td><?=$row['memberIdx']?></td>
                    <td>
                        <? if($row['isRead'] == 'Y') { ?>
                            <span style="padding: 4px 8px; display: inline-block; background-color: #d0f0d0; color: green;">확인</span>
                        <? } else { ?>
                            <span style="padding: 4px 8px; display: inline-block; background-color: #fce4e4; color: red;">미확인</span>
                        <? } ?>
                    </td>
                    <td>
                        <select onchange="updateScanStatus(<?= $row['mailboxIdx'] ?>, this.value)">
                            <option value="" <?= $row['scan_status'] === null ? 'selected' : '' ?>>미선택</option>
                            <option value="0" <?= $row['scan_status'] == 0 ? 'selected' : '' ?>>스캔</option>
                            <option value="1" <?= $row['scan_status'] == 1 ? 'selected' : '' ?>>스캔 & 택배</option>
                            <option value="2" <?= $row['scan_status'] == 2 ? 'selected' : '' ?>>택배</option>
                            <option value="3" <?= $row['scan_status'] == 3 ? 'selected' : '' ?>>보관</option> <!-- change 폐기 -> 보관 -->
                        </select>
                    </td>

                    <td class="left">
                        <p><?=$row['senderAddress']?></p>
                        <p><?=$row['senderName']?></p>
                    </td>
                    <!-- <td><?=$row['nickname'] ?: '-'?></td> -->
                    <td><?=$row['regDate']?></td>
                    <td>
                        <? if($row['pdfPath']) { ?>
                            <a href="/assets/mailbox/<?=$row['pdfPath']?>" target="_blank" class="btnDownload" style="padding: 4px 8px; display: inline-block;">PDF 보기</a>
                        <? } else { ?>
                            <button type="button" class="btnDownload" onclick="openUploadModal(<?=$row['mailboxIdx']?>)" style="padding: 4px 8px; display: inline-block; color: red;">스캔본 첨부</button>
                        <? } ?>
                    </td>
                    <!-- 스캔 결제 상태 -->
                    <td>
                        <? if ($row['isPaidScan'] === 'Y') { ?>
                            <span style="color: green;">결제완료</span>
                        <? } else { ?>
                            <span style="color: red;">미결제</span>
                        <? } ?>
                    </td>

                    <!-- 스캔 결제 수단 -->
                    <td>
                        <p><?= isset($payType[trim($row['scanPayType'])]) ? $payType[trim($row['scanPayType'])] : '-' ?></p>
                        <? if($row['isPaidScan'] == 'N' && $row['scanPayType'] == 'deposit') { ?> 
                                <a class="chkBtn" onclick="confirmScanDeposit(<?= $row['mailboxIdx'] ?>)">입금 확인</a>
                        <? } ?>
                        <? if($row['isCashReceiptScan'] == 'Y' && $row['isPaidScan'] == 'Y') { ?>
                            <button onclick="downScanCashReceipt(<?= $row['mailboxIdx'] ?>)" style="background: #03a603; color: #fff; padding: 5px;">현금영수증</button>
                        <? } ?>
                    </td>

                    <!-- 택배 결제 상태 -->
                    <td>
                        <? if ($row['isPaidDelivery'] === 'Y') { ?>
                            <span style="color: green;">결제완료</span>
                        <? } else { ?>
                            <span style="color: red;">미결제</span>
                        <? } ?>
                    </td>

                    <!-- 택배 결제 수단 -->
                    <td>
                        <p?><?= isset($payType[trim($row['deliveryPayType'])]) ? $payType[trim($row['deliveryPayType'])] : '-' ?></p>
                        <? if($row['isCashReceiptDelivery'] == 'Y' && $row['isPaidDelivery'] == 'Y') { ?>
                            <button onclick="downDeliveryCashReceipt(<?= $row['mailboxIdx'] ?>)" style="background: #03a603; color: #fff; padding: 5px;">현금영수증</button>
                        <? } ?>
                    </td>

                    <td><?= $row['deliveryRequest'] == 'Y' ? '신청' : '없음' ?></td>
                    <td><?= $row['deliveryRequest'] == 'Y' ? $row['receiverName'] : '-' ?></td>
                    <td><?= $row['deliveryRequest'] == 'Y' ? $row['receiverAddr'] : '-' ?></td>
                    <td><?= $row['deliveryRequest'] == 'Y' ? $row['receiverAddrDetail'] : '-' ?></td>
                    <td><?= $row['deliveryRequest'] == 'Y' ? $row['receiverTel'] : '-' ?></td>
                    <td>
                        <? if($row['trackingNumber']) { ?>
                            <div><?=$row['deliveryCompany'] . ' / ' . $row['trackingNumber']?></div>
                            <button style="margin-top: 5px;" onclick="setTrackingNumber(<?=$row['mailboxIdx']?>)">수정</button>
                        <? } else { ?>
                            <button onclick="setTrackingNumber(<?=$row['mailboxIdx']?>)">등록</button>
                        <? } ?>
                    </td>

                    <!-- <td>
                        <button onclick="openUploadModal(<?=$row['idx']?>)">📤 스캔파일 업로드</button>
                    </td> -->
                </tr>
            <? } ?>
        </tbody>
    </table>

</div>

<!-- 📤 스캔 업로드 모달 -->
<div id="uploadModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" style="width:800px;">
    <div class="modal-content">
      <div class="modal-body">
        <p><strong>스캔 파일 업로드 (PDF)</strong></p>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="hidden" name="mailboxIdx" id="uploadMailboxIdx">

            <!-- 드래그 앤 드롭 영역 -->
            <div id="dropZone" style="border: 2px dashed #aaa; padding: 20px; text-align: center; margin-top: 10px;">
                또는 여기에 PDF 파일을 드래그하여 올려주세요.
            </div>

            <!-- 파일 선택 입력창 -->
            <input type="file" name="pdfFile" id="pdfInput" accept=".pdf" style="display:none;" required />
            <button type="button" class="btnDownload" onclick="document.getElementById('pdfInput').click();">파일 첨부하기</button>

            <!-- 파일명 표시 -->
            <div id="fileName" style="margin-top: 10px; color: #333;">선택된 파일 없음</div>

            <br><br>
            <button type="submit" class="btnDownload">업로드</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- 📬 사서함 등록 모달 -->
<div id="registerModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:600px; max-width:90%;">
    <div class="modal-content">
      <div class="modal-body">
        <p><strong>사서함에 등록할 회원과 PDF 파일을 입력해주세요.</strong></p>

        <!-- 회원 이름 검색 영역 -->
        <div style="display:flex; gap:5px; margin-bottom:10px;">
          <input type="text" id="searchMemberName" class="fieldInput" placeholder="회원 이름 입력" style="flex:1;" />
          <button type="button" class="btnDownload" onclick="searchMember()">검색</button>
        </div>

        <form id="registerForm" enctype="multipart/form-data">
            <label>회원 번호</label>
            <input type="number" name="memberIdx" id="memberIdxInput" required class="fieldInput"><br><br>

            <label>보내는 사람 이름</label>
            <input type="text" name="senderName" class="fieldInput"><br><br>

            <label>보내는 사람 주소</label>
            <input type="text" name="senderAddress" class="fieldInput"><br><br>

            <button class="btnDownload" onclick="setTrackingNumber(<?=$row['mailboxIdx']?>)">등록</button>

        </form>
      </div>
    </div>
  </div>
</div>

<!-- 👤 회원 검색 결과 모달 -->
<div id="memberResultModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="width:800px; max-width:90%;">
        <div class="modal-content">
        <div class="modal-body">
            <p><strong>회원 목록</strong></p>
            <ul id="memberResultList" style="max-height: 300px; overflow-y: auto;"></ul>
        </div>
        </div>
  </div>
</div>


<!-- 🚚 운송장 등록 모달 -->
<div id="trackingModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" style="width:400px;">
    <div class="modal-content">
      <div class="modal-body">
        <p><strong>운송장 정보를 입력해주세요.</strong></p>
        <form id="trackingForm">
            <input type="hidden" name="mailboxIdx" id="trackingMailboxIdx">

            <label>택배 회사</label>
            <select name="deliveryCompany" id="deliveryCompany" class="fieldInput" required>
                <option value="">선택</option>
                <option value="CJ대한통운">CJ대한통운</option>
                <option value="우체국택배">우체국택배</option>
                <option value="한진택배">한진택배</option>
                <option value="롯데택배">롯데택배</option>
                <option value="로젠택배">로젠택배</option>
                <option value="쿠팡로켓">쿠팡로켓</option>
                <option value="기타">기타</option>
            </select><br><br>

            <label>운송장번호</label>
            <input type="text" name="trackingNumber" id="trackingNumber" class="fieldInput" required><br><br>

            <button type="submit" class="btnDownload">등록</button>
        </form>
      </div>
    </div>
  </div>
</div>



<script>

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('pdfInput');
    const fileNameDisplay = document.getElementById('fileName');

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.style.backgroundColor = '#f0f0f0';
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.style.backgroundColor = 'white';
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.style.backgroundColor = 'white';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;

            // 선택된 파일 이름을 보여주기
            dropZone.innerText = `선택된 파일: ${files[0].name}`;
        }
    });

    // 파일 선택 시 파일명 표시
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            fileNameDisplay.innerText = `선택된 파일: ${e.target.files[0].name}`;
        }
    });

    function openUploadModal(mailboxIdx) {
        $('#uploadMailboxIdx').val(mailboxIdx);
        $('#uploadModal').modal('show');
    }

    $('#uploadForm').submit(async function(e){
        e.preventDefault();
        let formData = new FormData(this);

        for (let pair of formData.entries()) {
            console.log('FormData Key:', pair[0], 'Value:', pair[1]);
        }

        const res = await fetch('/adminApi/uploadMailboxPdf', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        console.log(result);
        if(result.result) {
            alert('업로드 완료');
            location.reload();
        } else {
            alert(result.msg || '업로드 실패');
        }
    });

    function setTrackingNumber(mailboxIdx) {
        $('#trackingMailboxIdx').val(mailboxIdx);
        $('#deliveryCompany').val('');
        $('#trackingNumber').val('');
        $('#trackingModal').modal('show');
    }

    $('#trackingForm').submit(async function(e){
        e.preventDefault();
        
        const mailboxIdx = $('#trackingMailboxIdx').val();
        const deliveryCompany = $('#deliveryCompany').val();
        const trackingNumber = $('#trackingNumber').val();

        if (!deliveryCompany || !trackingNumber) {
            alert('택배회사와 운송장번호를 모두 입력해주세요.');
            return;
        }

        const res = await fetch('/adminApi/setTrackingNumber', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mailboxIdx, deliveryCompany, trackingNumber })
        });

        const result = await res.json();
        if(result.result) {
            alert('등록 완료');
            location.reload();
        } else {
            alert(result.msg || '등록 실패');
        }
    });

    /* 현금영수증 정보 다운로드 */
    function downScanCashReceipt(mailboxIdx) {
        const row = <?= json_encode($list) ?>.find(r => r.mailboxIdx == mailboxIdx);
        if (!row) {
            alert("대상 정보를 찾을 수 없습니다.");
            return;
        }

        // 날짜 포맷 (예: 20250404)
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');



        const wb = XLSX.utils.book_new();

        const data = [
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
                row.cashReceiptTypeScan === 'earnings' ? '0' : '1',
                row.cashReceiptNumberScan || '',
                1000,
                1000,
                0,
                `사서함 스캔본 결제 - ${row.cashReceiptEmailScan || ''}`,
                "",
                ""
            ]
        ];

        const ws = XLSX.utils.aoa_to_sheet(data);

        ws['!cols'] = [
            { wch: 15 }, { wch: 25 }, { wch: 20 }, { wch: 20 },
            { wch: 15 }, { wch: 40 }, { wch: 10 }, { wch: 10 }
        ];

        XLSX.utils.book_append_sheet(wb, ws, '현금영수증');
        const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

        function s2ab(s) {
            const buf = new ArrayBuffer(s.length);
            const view = new Uint8Array(buf);
            for (let i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xff;
            return buf;
        }

        saveAs(
            new Blob([s2ab(wbout)], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }),
            `${yyyy}${mm}${dd}.xlsx`
        );
    }

    function downDeliveryCashReceipt(mailboxIdx) {
        const row = <?= json_encode($list) ?>.find(r => r.mailboxIdx == mailboxIdx);
        if (!row) {
            alert("대상 정보를 찾을 수 없습니다.");
            return;
        }

        // 날짜 포맷 (예: 20250404)
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');



        const wb = XLSX.utils.book_new();

        const data = [
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
                row.cashReceiptTypeDelivery === 'earnings' ? '0' : '1',
                row.cashReceiptNumberDelivery || '',
                4000,
                4000,
                0,
                `사서함 택배 결제 - ${row.cashReceiptEmailDelivery || ''}`,
                "",
                ""
            ]
        ];

        const ws = XLSX.utils.aoa_to_sheet(data);

        ws['!cols'] = [
            { wch: 15 }, { wch: 25 }, { wch: 20 }, { wch: 20 },
            { wch: 15 }, { wch: 40 }, { wch: 10 }, { wch: 10 }
        ];

        XLSX.utils.book_append_sheet(wb, ws, '현금영수증');
        const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

        function s2ab(s) {
            const buf = new ArrayBuffer(s.length);
            const view = new Uint8Array(buf);
            for (let i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xff;
            return buf;
        }

        saveAs(
            new Blob([s2ab(wbout)], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }),
            `${yyyy}${mm}${dd}.xlsx`
        );
    }

    function openRegisterModal() {
        $('#registerModal').modal('show');
    }

    $('#registerForm').submit(async function(e){
        e.preventDefault();
        let formData = new FormData(this);

        const res = await fetch('/adminApi/registerMailbox', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();
        if(result.result) {
            alert('등록 완료');
            location.reload();
        } else {
            alert(result.msg || '등록 실패');
        }
    });

    async function confirmScanDeposit(mailboxIdx) {
        const confirmed = confirm("해당 사용자의 스캔 결제를 입금 확인 처리하시겠습니까?");
        if (!confirmed) return;

        const res = await fetch('/adminApi/confirmScanDeposit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idx: mailboxIdx })
        });

        const result = await res.json();

        if (result.result) {
            alert("입금 확인이 완료되었습니다.");
            location.reload();
        } else {
            alert(result.msg || "입금 확인에 실패했습니다.");
        }
    }

    // 회원 검색 버튼 클릭
    async function searchMember() {
        const keyword = $('#searchMemberName').val().trim();
        if (!keyword) {
            alert("회원 이름을 입력해주세요.");
            return;
        }

        const res = await fetch('/adminApi/searchMembersByName', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ keyword })
        });

        const text = await res.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            alert('검색 결과를 불러오지 못했습니다.');
            console.error('JSON 파싱 오류:', text);
            return;
        }

        if (result.result && result.data.length) {
            const html = result.data.map(member => `
                <li style="padding:8px; border-bottom:1px solid #eee; cursor:pointer;"
                    onclick="selectSearchMember(${member.idx}, '${member.senderName}')">
                    ${member.senderName} (${member.idx}) - ${member.senderTel || ''} - ${member.receiverAddr} ${member.receiverAddrDetail}
                </li>
            `).join('');
            $('#memberResultList').html(html);
            $('#memberResultModal').modal('show');
        } else {
            $('#memberResultList').html('<li style="padding:8px;">검색 결과가 없습니다.</li>');
            $('#memberResultModal').modal('show');
        }
    }

    // 선택된 회원을 등록폼에 넣기
    function selectSearchMember(memberIdx, memberName) {
        $('#memberIdxInput').val(memberIdx);
        $('#memberResultModal').modal('hide');
        // alert(`"${memberName}" 회원이 선택되었습니다.`);
    }

    async function updateScanStatus(idx, status) {
        console.log(idx, status);

        const res = await fetch('/adminApi/updateScanStatus', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idx, status })
        });

        const result = await res.json();

        console.log('서버 응답: ', result);
        if (result.result) {
            location.reload(); // 혹은 동적으로 갱신할 수도 있음
        } else {
            alert(result.msg || '상태 변경에 실패했습니다.');
        }
    }


</script>

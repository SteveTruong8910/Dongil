<link rel="stylesheet" type="text/css" href="/assets/css/view/tmp.css<?=$this->config->item('ver')?>">
<style>
    #ft_menu { display: none; }
    .mailItem { position: relative; display: flex; border: 1px solid #ddd; margin-bottom: 15px; padding: 10px; border-radius: 5px; cursor: pointer; }
    .rightBox { flex: 1; padding-left: 15px; }
    .btnWrap { margin-top: 10px; }
    .downloadBtn, .deliveryBtn { display: inline-block; margin-right: 10px; padding: 6px 12px; border-radius: 4px; background-color: #555; color: #fff; text-decoration: none; }
    .requested { color: green; font-weight: bold; }
    .modalOverlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .modalContent {
        background: white;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
    }

    .btnWrap {
        display: flex;               /* 버튼을 가로로 나열 */
        flex-direction: column;      /* 세로로 배치 */
        gap: 15px;                   /* 버튼 사이에 간격 추가 */
        align-items: flex-start;     /* 버튼들을 왼쪽으로 정렬 */
    }

    .btnWrap .btnDownload {
        padding: 10px 20px;          /* 버튼 크기 조정 */
        border-radius: 5px;          /* 둥근 모서리 */
        background-color: #555;      /* 버튼 배경색 */
        color: #fff;                 /* 버튼 텍스트 색 */
        text-decoration: none;       /* 밑줄 없애기 */
        font-size: 16px;             /* 텍스트 크기 */
        cursor: pointer;            /* 마우스 커서 포인터 */
        border: none;                /* 테두리 제거 */
        transition: background-color 0.3s ease; /* 버튼 색상 변화 효과 */
    }

    .btnWrap .btnDownload:hover {
        background-color: #444;      /* 마우스를 올렸을 때 버튼 배경색 변화 */
    }

    /* 요청된 택배인 경우 다른 색상 */
    .btnWrap .requested {
        background-color: #28a745;
    }

    .btnWrap .requested:hover {
        background-color: #218838;
    }
</style>

<div id="mailbox">
    <div class="mailboxWrap">
    <?php if (!count($mailList)) { ?>
        <p class="empty">사서함이 비어있어요!</p>
    <?php } else { ?>
    <strong>총 <?=count($mailList)?>건</strong>
        <?php foreach($mailList as $mail) { ?>
            <div class="mailItem"
                data-idx="<?=$mail['idx']?>"
                data-sender="<?=htmlspecialchars($mail['senderName'])?>"
                data-address="<?=htmlspecialchars($mail['senderAddress'])?>"
                data-date="<?=$mail['regDate']?>"
                data-path="<?=$mail['pdfPath']?>"
                data-request="<?=$mail['deliveryRequest']?>"
                data-read="<?=$mail['isRead']?>"
                data-discarded="<?=isset($mail['isDiscarded']) ? $mail['isDiscarded'] : null ?>"
                data-scan-status="<?=$mail['scan_status']?>"> <!-- scanStatus 값 추가 -->

                <?php if ($mail['isRead'] == 'N') { ?>
                    <img src="/assets/image/new.png" alt="New" style="position: absolute; top: 0px; left: 0px; width: 40px;">
                <?php } ?>
                <div class="rightBox">
                    <p><strong>보낸 사람:</strong> <?=htmlspecialchars($mail['senderName']) ?: '-'?></p>
                    <p><strong>보낸 주소:</strong> <?=htmlspecialchars($mail['senderAddress']) ?: '-'?></p>
                    <p><strong>보낸 날짜:</strong> <?=$mail['regDate']?></p>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
    </div>
</div>

<!-- 모달 -->
<div class="modalOverlay" id="mailModal">
  <div class="modalContent">
    <p id="modalSender"></p>
    <p id="modalAddress"></p>
    <p id="modalDate"></p>
    <div class="btnWrap" id="modalButtons"></div>
    <div style="text-align:right; margin-top: 10px;">
      <button onclick="closeModal()">닫기</button>
    </div>
  </div>
</div>

<script>

    // 상태에 따라 버튼을 다르게 표시
    function renderButtons(scanStatus, mailboxIdx) {
        let buttons = '';

        if (scanStatus === null) {
            // 스캔 상태가 설정되지 않은 경우 (null)
            buttons = `
                <button class="btnDownload" onclick="setScanStatus(0, ${mailboxIdx})">스캔본 확인 후 폐기하기</button>
                <button class="btnDownload" onclick="setScanStatus(1, ${mailboxIdx})">스캔본 확인 후 택배받기</button>
                <button class="btnDownload" onclick="setScanStatus(2, ${mailboxIdx})">스캔본 확인 안하고 택배받기</button>
                <button class="btnDownload" onclick="setScanStatus(3, ${mailboxIdx})">스캔본 확인 안하고 폐기하기</button>
            `;
        } else {
            // 상태 값에 따라 다른 버튼을 표시
            switch(scanStatus) {
                case 0:
                    buttons = `
                        <button class="btnDownload" onclick="downloadScan(${mailboxIdx})">스캔본 다운로드</button>
                    `;
                    break;
                case 1:
                    buttons = `
                        <button class="btnDownload" onclick="downloadScan(${mailboxIdx})">스캔본 다운로드</button>
                        <button class="btnDownload" onclick="requestDelivery(${mailboxIdx})">택배 신청</button>
                    `;
                    break;
                case 2:
                    buttons = `
                        <button class="btnDownload" onclick="requestDelivery(${mailboxIdx})">택배 신청</button>
                    `;
                    break;
                case 3:
                    // '스캔본 확인 안하고 폐기하기' 상태에서는 더 이상 다른 버튼이 보이지 않음
                    buttons = '';
                    break;
            }
        }

        return buttons;
    }

    // 상태값 업데이트 함수
    function setScanStatus(status, mailboxIdx) {
        fetch(`/userApi/updateMailboxStatus`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mailboxIdx, scanStatus: status })
        })
        .then(response => response.json())
        .then(result => {
            if (result.result) {
                alert('스캔본 상태가 설정되었습니다.');

                // 상태를 갱신한 후, 해당 상태에 맞는 버튼을 동적으로 다시 렌더링
                const buttons = document.getElementById('modalButtons');
                buttons.innerHTML = '';  // 기존 버튼 제거
                updateButtons(status, mailboxIdx, buttons, result.isRequested);  // 버튼 다시 렌더링
            } else {
                alert(result.msg || '스캔본 상태 설정 실패');
            }
        });
    }



    // '스캔본 확인 안하고 폐기하기' 상태로 변경하는 함수
    function setScanStatusToDelete(mailboxIdx) {
        fetch(`/userApi/setMailboxToDelete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mailboxIdx })
        })
        .then(response => response.json())
        .then(result => {
            if (result.result) {
                alert('해당 사서함이 폐기되었습니다.');
                location.reload();
            } else {
                alert(result.msg || '폐기 처리 실패');
            }
        });
    }

    async function requestDelivery(mailIdx) {
        console.log(mailIdx);
        const res = await fetch('/userApi/checkMailPayment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idx: mailIdx, payType: 'delivery' })
        });

        const result = await res.json();
        console.log(result); 

        if (!result.result) {
            showAlert(result.msg || "결제 확인 실패");
            return;
        }

        if (!result.isPaid) {
            location.href = `/mypage/payment?type=delivery&mailboxIdx=${mailIdx}`;
            return;
        }

        const confirmed = await showConfirm("택배로 받으시겠습니까?");
        if (!confirmed.value) return;

        const deliveryRes = await postJson('/userApi/requestMailDelivery', { mailIdx });

        if (!deliveryRes.result) {
            showAlert(deliveryRes.msg || "신청 실패");
            return;
        }

        showAlert("택배 신청 완료").then(() => {
            location.reload();
        });
    }

    async function requestDeliveryDone(mailIdx) {
        const res = await fetch('/userApi/getTrackingInfo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idx: mailIdx })
        });
        console.log("운송장조회 mailIdx:", mailIdx);


        const result = await res.json();

        if (!result.result) {
            showAlert(result.msg || "운송장 정보를 불러올 수 없습니다.");
            return;
        }

        const { deliveryCompany, trackingNumber, deliveryDate } = result;

        let msg = '';
        if (!deliveryCompany && !trackingNumber) {
            msg = '📦 아직 운송장 정보가 등록되지 않았습니다.';
        } else {
            msg = `
                <strong>택배사:</strong> ${deliveryCompany || '-'}<br>
                <strong>운송장번호:</strong> ${trackingNumber || '-'}<br>
                <strong>발송일:</strong> ${deliveryDate || '-'}
            `;
        }

        showAlert(msg);
    }


    async function handleScanDownload(mailIdx) {
        const res = await fetch('/userApi/checkMailPayment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idx: mailIdx, payType: 'scan' })
        });

        const result = await res.json();

        if (!result.result) {
            showAlert(result.msg || "결제 확인 실패");
            return;
        }

        console.log(result);

        // PDF 파일 경로가 없을 경우 alert
        if (!result.pdfPath) {
            showAlert("PDF 파일 첨부 대기중입니다.<br>관리자가 곧 첨부할 예정입니다.");
            return;
        }

        if (!result.isPaid) {
            if (result.payType === 'deposit') {
                showAlert("입금 확인 요청 중입니다. 잠시만 기다려주세요.");
            } else {
                location.href = `/mypage/payment?type=scan&mailboxIdx=${mailIdx}`;
            }
            return;
        }

        // 결제 완료된 경우 실제 경로 조회
        const fileRes = await fetch('/userApi/getMailboxFilePath', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idx: mailIdx })
        });

        const fileInfo = await fileRes.json();
        console.log("fileInfo:", fileInfo); 

        if (!fileInfo.result) {
            showAlert(fileInfo.msg || "파일 경로 확인 실패");
            return;
        }

        window.open('/assets/mailbox/' + fileInfo.pdfPath, '_blank');
    }



    function closeModal() {
        document.getElementById('mailModal').style.display = 'none';
    }

    const mailItems = document.querySelectorAll('.mailItem');
    mailItems.forEach(item => {
        item.addEventListener('click', async () => {
            const idx = item.dataset.idx;
            const sender = item.dataset.sender;
            const address = item.dataset.address;
            const date = item.dataset.date;
            const path = item.dataset.path;
            const request = item.dataset.request;
            
            const isRead = item.dataset.read === 'Y';
            const isDiscarded = item.dataset.discarded === 'Y';
            const isRequested = request === 'Y';

            // scanStatus 값을 제대로 가져옵니다.
            const scanStatus = item.dataset.scanStatus;  // 여기에서 data-scan-status 값을 가져옵니다.

            console.log("현재 스캔 상태:", scanStatus);  // 값 확인을 위한 로그 추가

            document.getElementById('modalSender').innerText = '보낸 사람: ' + (sender || '-');
            document.getElementById('modalAddress').innerText = '보낸 주소: ' + (address || '-');
            document.getElementById('modalDate').innerText = '보낸 날짜: ' + date;

            const buttons = document.getElementById('modalButtons');
            buttons.innerHTML = '';

            // 상태에 맞는 버튼 그룹
            updateButtons(scanStatus, idx, buttons, isRequested);  // scanStatus 값을 사용하여 버튼 렌더링

            // 마크 읽기 처리
            await fetch('/userApi/markAsRead', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idx })
            });

            const newIcon = item.querySelector('img[alt="New"]');
            if (newIcon) newIcon.remove();
        });
    });

    const makeBtn = (text, handler, className = 'deliveryBtn') => {
        const btn = document.createElement('a');
        btn.className = className;
        btn.innerText = text;
        btn.href = '#';
        btn.onclick = function(e) {
            e.preventDefault();
            handler();
        };
        return btn;
    };

    // 상태값 업데이트 함수
    function updateScanStatus(status, mailboxIdx) {
        fetch(`/userApi/updateMailboxStatus`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mailboxIdx, scanStatus: status })
        })
        .then(response => response.json())
        .then(result => {
            if (result.result) {
                alert('스캔본 상태가 설정되었습니다.');
                const buttons = document.getElementById('modalButtons');
                buttons.innerHTML = '';  // 기존 버튼 제거
                updateButtons(status, mailboxIdx, buttons, result.isRequested);  // 버튼 다시 렌더링
            } else {
                alert(result.msg || '스캔본 상태 설정 실패');
            }
        });
    }

    // 상태에 맞는 버튼 그룹
    const updateButtons = async (scanStatus, mailboxIdx, buttons, isRequested) => {
        console.log("현재 스캔 상태:", scanStatus);  // 상태 값 확인

        buttons.innerHTML = '';

        if (scanStatus === '') {
            // 스캔 상태가 설정되지 않은 경우
            buttons.appendChild(makeBtn('스캔본 확인 후 폐기하기', () => updateScanStatus('0', mailboxIdx)));
            buttons.appendChild(makeBtn('스캔본 확인 후 택배받기', () => updateScanStatus('1', mailboxIdx)));
            buttons.appendChild(makeBtn('스캔본 확인하지 않고 택배받기', () => updateScanStatus('2', mailboxIdx)));
            // buttons.appendChild(makeBtn('스캔본 확인하지 않고 폐기하기', () => discardMail(mailboxIdx)));
            buttons.appendChild(makeBtn('스캔본 확인 후 보관하기', () => updateScanStatus('3', mailboxIdx)));
        } else {
            // 상태에 따른 버튼 그룹
            switch(scanStatus) {
                case '0':  // 스캔본 확인 후 폐기하기
                    buttons.appendChild(makeBtn('스캔본 다운로드', () => handleScanDownload(mailboxIdx)));
                    break;
                case '1':  // 스캔본 확인 후 택배받기
                    buttons.appendChild(makeBtn('스캔본 다운로드', () => handleScanDownload(mailboxIdx)));
                    buttons.appendChild(makeBtn('택배 신청', () => requestDelivery(mailboxIdx)));
                    break;
                case '2':  // 스캔본 확인 안하고 택배받기
                    buttons.appendChild(makeBtn('택배 신청', () => requestDelivery(mailboxIdx)));
                    break;
                case '3':  // 스캔본 확인 안하고 폐기하기
					buttons.appendChild(makeBtn('스캔본 다운로드', () => handleScanDownload(mailboxIdx)));
                    break;
            }
        }

        if (isRequested) {
            buttons.appendChild(makeBtn('🚚 운송장 번호 조회', () => requestDeliveryDone(mailboxIdx), 'requested'));
        }

        document.getElementById('mailModal').style.display = 'flex';
    };




    async function discardMail(mailIdx) {
        console.log(mailIdx);
        const confirmed = await showConfirm("정말 폐기하시겠습니까?");
        if (!confirmed.value) return;

        try {
            const res = await fetch('/userApi/discardMail', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mailIdx })
            });

            const result = await res.json();

            if (result.result) {
                showAlert("폐기 처리 완료되었습니다.").then(() => {
                    location.reload();
                });
            } else {
                showAlert(result.msg || "폐기 처리 중 오류가 발생했습니다.");
            }
        } catch (e) {
            console.error("폐기 요청 오류:", e);
            showAlert("요청 중 오류가 발생했습니다.");
        }
    }


   

</script>

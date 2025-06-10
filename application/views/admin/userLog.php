<p class="title">로그확인</p>

<div id="notice">
    <div class="local">
        총 로그 수 <?=$pageData['totalCnt']?>건
    </div>

    <div id="letter">
        <div class="cateBox" style="margin-top: 20px;">
            <a href="/admin/userLog?type=login" class="<?=$type == 'login' ? 'active' : ''?>">로그인 로그</a>
            <a href="/admin/userLog?type=access" class="<?=$type == 'access' ? 'active' : ''?>">접속 로그</a>
        </div>
    </div>

    <form id="searchForm" method="get">
        <div class="filter">
            <input type="hidden" name="page" value="1"/>
        </div>
    </form>


    <!-- 로그 테이블 -->
    <table style="margin-top:10px;">
        <thead>
            <tr>
                <th width="150">아이디</th>
                <th width="150">이름</th>
                <th width="150">IP 주소</th>
                <?php if ($type == 'login') { ?>
                    <th width="200">로그인 방법</th>
                    <th width="100">결과</th>
                    <th width="250">실패 사유</th>
                    <th width="200">로그인 시간</th>
                <?php } else if ($type == 'access') { ?>
                    <th width="200">접속 시간</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <!-- 로그가 없는 경우 -->
            <?php if (!count($list)) { ?>
                <tr>
                    <td colspan="<?= ($type == 'login') ? '7' : '4' ?>" class="empty">
                        <?= ($type == 'login') ? '로그인 로그 내역이 존재하지 않습니다.' : '접속 로그 내역이 존재하지 않습니다.' ?>
                    </td>
                </tr>
            <?php } ?>

            <!-- 로그 출력 -->
            <?php foreach ($list as $key => $data) { ?>
                <tr>
                    <td><?=$data['user_id']?></td>
                    <td><?=$data['username']?></td>
                    <td><?=$data['ip_address']?></td>
                    <?php if ($type == 'login') { ?>
                        <td><?=$data['sns']?></td>
                        <td><?=$data['status']?></td>
                        <td><?=$data['reason']?></td>
                        <td><?=$data['login_date']?></td>
                    <?php } else if ($type == 'access') { ?>
                        <td><?=$data['access_date']?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
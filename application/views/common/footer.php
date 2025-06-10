<?
    $current_controller = $this->router->fetch_class();
    $current_method = $this->router->fetch_method();
?>
            <div id="ft_menu">                
                <a href="javascript:nav.locationHref('/', 'clear')">
                    <i class="fas fa-home <?=($current_controller == 'home' && $current_method == 'index')? 'active' : ''?>"></i>
                </a>
                                 
                <a href="javascript:nav.locationHref('/letter', 'b1')">
                    <i class="fas fa-envelope"></i>
                </a>          
                                
                <a href="javascript:nav.locationHref('/board', 'c1')">
                    <i class="fas fa-bullhorn <?=($current_controller == 'board')? 'active' : ''?>"></i>
                </a>                
                
                                
                <a href="javascript:nav.locationHref('/mypage', 'd1')">
                    <i class="fas fa-user <?=($current_controller == 'mypage')? 'active' : ''?>"></i>
                </a>                
            </div>
        </div> <!--mainWrap-->
	</body>		
</html>
<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body">

<div class="clearfix"></div>


<form>
<!-- <div class="panel panel-default"> -->
<!-- <div class="panel-body"> -->

<!-- 形式切り替えと追加 (上部) -->
<?php echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'member', 'menuPosition' => 'top')); ?>

<div class="row"><!--全体枠-->

				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarSchedule->makeBodyHtml($vars);
				?>



  
  <h2>スケジュール（会員順） </h2> 
  
  <div class="row calendar-tablecontainer">
  <div class="col-xs-12 col-sm-2"><p class="calendar-schedule-membername"><a>橋本さん橋本さん</a></p></div>
  <div class="col-xs-12 col-sm-10">
  <table class="table table-hover calendar-tablestyle">
   <tbody>
    <tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr>
<tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr><tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr>
   </tbody>
  </table>
    </div>
    </div>
    <div class="row calendar-tablecontainer">
  <div class="col-xs-12 col-sm-2"><p class="calendar-schedule-membername"><a>橋本さん橋本さん</a></p></div>
  <div class="col-xs-12 col-sm-10">
  <table class="table table-hover calendar-tablestyle">
   <tbody>
    <tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr>
<tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr><tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr>
   </tbody>
  </table>
    </div>
    </div>
    <div class="row calendar-tablecontainer">
  <div class="col-xs-12 col-sm-2"><p class="calendar-schedule-membername"><a>橋本さん橋本さん</a></p></div>
  <div class="col-xs-12 col-sm-10">
  <table class="table table-hover calendar-tablestyle">
   <tbody>
    <tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr>
<tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr><tr><td>
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</td>
</tr>
   </tbody>
  </table>
    </div>
    </div>
</div><!--全体枠END-->

<!-- 形式切り替えと追加 (下部) -->
<?php //echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'member', 'menuPosition' => 'bottom')); ?>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

	<!-- 予定の内容 -->
	<?php
		echo $this->CalendarLegend->getCalendarLegend($vars);
	?>

</article>

<script type='text/javascript'>
/* 画面設計用. */

var mock= {};
mock.elms = document.getElementsByClassName('calendar-schedule-disp');
mock.fnc =  function(evt) {

	console.log( '[' + evt.target.tagName + '] [' + evt.target.getAttribute('class') + ']');


	var target = evt.target;

	if ( target.tagName != 'P' ) {
		for( ; ; ) {
			target = target.parentNode;
			if (target.nodeType != Node.ELEMENT_NODE) {
				continue;
			}

			if( target.tagName == 'P') {
				break;
			}

			if( target.tagName == 'BODY') {
				return;
			}
		}
	}

	var stat = target.getAttribute('data-openclose-stat');
	var num = target.getAttribute('data-pos');

	if (stat == 'open') {

		var elms2 = document.getElementsByClassName('schedule-openclose');
		console.log('openケース[' + elms2.length + ']');

		for(var i = 0; i < elms2.length; ++i) {
			var elm = elms2[i];
			if (elm.getAttribute('data-pos') == num) {
				elm.className = 'glyphicon glyphicon-menu-right schedule-openclose';
				console.log('アイコンをcloseにしました。');
			}
		}

		elms2 = document.getElementsByClassName('calendar-schedule-row');
		for(var i = 0; i < elms2.length; ++i) {
			var elm = elms2[i];
			if (elm.getAttribute('data-pos') == num) {
				elm.style.display = 'none';
			}
		}

		//mock.elms[0].setAttribute('data-openclose-stat','close');
		target.setAttribute('data-openclose-stat','close');

		console.log('open case');

	} else if (stat == 'close') {

		var elms2 = document.getElementsByClassName('schedule-openclose');
		console.log('closeケース[' + elms2.length + ']');

		for(var i = 0; i < elms2.length; ++i) {
			var elm = elms2[i];
			if (elm.getAttribute('data-pos') == num) {
				elm.className = 'glyphicon glyphicon-menu-down schedule-openclose';
				console.log('アイコンをopenにしました。');
			}
		}

		elms2 = document.getElementsByClassName('calendar-schedule-row');
		for(var i = 0; i < elms2.length; ++i) {
			var elm = elms2[i];
			if (elm.getAttribute('data-pos') == num) {
				elm.style.display = 'block';
			}
		}

		//mock.elms[0].setAttribute('data-openclose-stat','open');
		target.setAttribute('data-openclose-stat','open');

		console.log('close case');
	} else {
		console.log('other case[' + stat + ']');
	}
};

for(var i=0; i < mock.elms.length; ++i) {
	mock.elms[i].addEventListener('click', mock.fnc );
}

</script>

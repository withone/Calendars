<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body">

<div class="clearfix"></div>


<form>
<!-- <div class="panel panel-default"> -->
<!-- <div class="panel-body"> -->

<!-- 形式切り替えと追加 (上部) -->
<?php echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'time', 'menuPosition' => 'top')); ?>

<div class="row"><!--全体枠-->

<!-- today -->
	<div class="col-sm-12 text-center">

		<?php
			// 予定の日付タイトル
			echo $this->CalendarSchedule->makeDayTitleHtml($vars, 1);
		?>
<!-- original
		<div class='row'>
			<div class='col-xs-12'>
				<p data-openclose-stat='open' data-pos='1' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>
				<span class='h4'><span data-pos='1' class="glyphicon glyphicon-chevron-down schedule-openclose"></span><span>今日</span><span style='margin-left: 0.5em'>(3)</span></span>
				</p>
			</div>
			<div class='clearfix'></div>
		</div>
-->

		<div class='row calendar-schedule-row' data-pos='1'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='calendar-plan-mark calendar-plan-mark-public'></span><span>会社HPメンテナンス</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>山田太郎</span></p>
			</div>
			<div class='clearfix'></div>
		</div>
		
		<div class='row calendar-schedule-row' data-pos='1'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='calendar-plan-mark calendar-plan-mark-private'></span><span class='label label-info'>一時保存</span><span>家族で外食予定</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>徳川家康</span></p>
			</div>
			<div class='clearfix'></div>
		</div>
		
		<div class='row calendar-schedule-row' data-pos='1'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>09:30-12:00</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-warning'>承認待ち</span><span>開発部定例会議</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>鈴木イチロー</span></p>
			</div>
			<div class='clearfix'></div>
		</div>
	</div>


<!-- tomorrow -->
	<div class="col-sm-12 text-center">

		<?php
			// 予定の日付タイトル
			echo $this->CalendarSchedule->makeDayTitleHtml($vars, 2);
		?>

<!-- original
		<div class='row'>
			<div class='col-xs-12'>
				<p data-openclose-stat='open' data-pos='2' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>
				<span class='h4'><span data-pos='2' class="glyphicon glyphicon-chevron-down schedule-openclose"></span><span>明日</span><span style='margin-left: 0.5em'>(3)</span></span>
				</p>
			</div>
			<div class='clearfix'></div>
		</div>
-->

		<div class='row calendar-schedule-row'  data-pos='2'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>進捗会議</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>鈴木イチロー</span></p>
			</div>
			<div class='clearfix'></div>
		</div>
		
		<div class='row calendar-schedule-row'  data-pos='2'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>19:00-21:00</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>佐藤さん送別会</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>徳川家康</span></p>
			</div>
			<div class='clearfix'></div>
		</div>

		<div class='row calendar-schedule-row'  data-pos='2'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>23:00-24:00</small></span><span class="glyphicon glyphicon-share-alt"></span><span>２月のクラス会skype打合せ</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>野茂秀雄</span></p>
			</div>
			<div class='clearfix'></div>
		</div>

	</div>


<!-- 3dayslater -->
	<div class="col-sm-12 text-center">

		<?php
			// 予定の日付タイトル
			echo $this->CalendarSchedule->makeDayTitleHtml($vars, 3);
		?>

<!--
		<div class='row'>
			<div class='col-xs-12'>
				<p data-openclose-stat='open' data-pos='3' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>
				<span class='h4'><span data-pos='3' class="glyphicon glyphicon-chevron-down schedule-openclose"></span><span>1月29日(金)</span><span style='margin-left: 0.5em'>(1)</span></span>
				</p>
			</div>
			<div class='clearfix'></div>
		</div>
-->


		<div class='row calendar-schedule-row'  data-pos='3'>
			<div class='col-xs-12 col-sm-9'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span class='calendar-plan-mark calendar-plan-mark-private'></span><span class="glyphicon glyphicon-share"><span>ホットヨガ教室（夜の部）下見</span></p>
			</div>
			<div class='col-xs-12 col-sm-3'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'><span class='text-success'>徳川家康</span></p>
			</div>
			<div class='clearfix'></div>
		</div>
		
	</div>

<!-- 4dayslater -->
	<div class="col-sm-12 text-center">

		<?php
			// 予定の日付タイトル
			echo $this->CalendarSchedule->makeDayTitleHtml($vars, 4);
		?>

<!--
		<div class='row'>
			<div class='col-xs-12'>
				<p data-openclose-stat='open' data-pos='4' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>
				<span class='h4'><span data-pos='4' class="glyphicon glyphicon-chevron-down schedule-openclose"></span><span class='text-primary'>2月1日(土)</span><span style='margin-left: 0.5em'></span></span>
				</p>
			</div>
			<div class='clearfix'></div>
		</div>
-->

		<div class='row calendar-schedule-row'  data-pos='4'>
			<div class='col-xs-12'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span>予定はありません</span></p>
			</div>
			<div class='clearfix'></div>
		</div>

	</div>

<!-- 5dayslater -->
	<div class="col-sm-12 text-center">

		<?php
			// 予定の日付タイトル
			echo $this->CalendarSchedule->makeDayTitleHtml($vars, 5);
		?>

<!--
		<div class='row'>
			<div class='col-xs-12'>
				<p data-openclose-stat='open' data-pos='5' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>
				<span class='h4'><span data-pos='5' class="glyphicon glyphicon-chevron-down schedule-openclose"></span><span class='text-danger'>2月2日(日)</span><span style='margin-left: 0.5em'></span></span>
				</p>
			</div>
			<div class='clearfix'></div>
		</div>
-->

		<div class='row calendar-schedule-row'  data-pos='5'>
			<div class='col-xs-12'>
				<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span>予定はありません</span></p>
			</div>
			<div class='clearfix'></div>
		</div>
	</div>

</div><!--全体枠END-->

<!-- 形式切り替えと追加 (下部) -->
<?php echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'time', 'menuPosition' => 'bottom')); ?>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

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
				elm.className = 'glyphicon glyphicon-chevron-right schedule-openclose';
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
				elm.className = 'glyphicon glyphicon-chevron-down schedule-openclose';
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

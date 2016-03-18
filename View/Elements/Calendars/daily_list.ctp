<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<!-- <div class="clearfix"></div> -->

<?php echo $this->element('Calendars.Calendars/daily_tabs', array('active' => 'list', 'frameId' => $frameId, 'languageId' => $languageId)); ?>


<div class="row"><!--全体枠-->
	<div class="col-xs-12 text-center">

		<table class='calendar-daily-nontimeline-table'>
			<tbody>

				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarDaily->makeDailyListBodyHtml($vars);
				?>


<!---    予定詳細
				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
						<div class='row'>
							<div class='col-xs-12'>
								<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>
									<span class='calendar-plan-mark calendar-plan-mark-public'></span>
									<span>成人の日祝賀会</span>
								</p>
							</div>
							<div class='clearfix'></div>
						</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
						<div class='row'>
							<div class='col-xs-12'>
								<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>
									<span class='calendar-plan-mark calendar-plan-mark-private'></span>
									<span>家族で外食予定</span>
								</p>
							</div>
							<div class='clearfix'></div>
						</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
						<div class='row'>
							<div class='col-xs-12'>
								<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>
									<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>09:30-12:00</small></span>
									<span class='calendar-plan-mark calendar-plan-mark-group'></span>
									<span>港区成人式参列</span>
								</p>
							</div>
						<div class='clearfix'></div>
						</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
						<div class='row'>
							<div class='col-xs-12'>
								<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>
									<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>11:00-17:00</small></span>
									<span class='calendar-plan-mark calendar-plan-mark-group'></span>
									<span class='label label-info'>一時保存</span><span>A社システム入れ替え作業</span>
								</p>
							</div>
							<div class='clearfix'></div>
						</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
						<div class='row'>
							<div class='col-xs-12'>
								<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>
									<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>16:00-17:30</small></span>
									<span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-warning'>承認待ち</span>
									<span>1月12日コンペ予行演習</span></p>
							</div>
							<div class='clearfix'></div>
						</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
						<div class='row'>
							<div class='col-xs-12'>
								<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>
									<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>22:00-24:00</small></span>
									<span class='calendar-plan-mark calendar-plan-mark-private'></span>
									<span>男子テニス準決勝中継TV観戦</span>
								</p>
							</div>
							<div class='clearfix'></div>
						</div>
					</td>
				</tr>
--->

			</tbody>
		</table>

	</div>
</div><!--全体枠END-->

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

</article>


<div class="row"><!--全体枠-->
	<div class="col-xs-12">
  
 <table class="table table-hover">
   <tbody>
   				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarDaily->makeDailyListBodyHtml($vars);
				?>

   </tbody>
  </table>

	</div>
</div><!--全体枠END-->

  

</form>
</article>

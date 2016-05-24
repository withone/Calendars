/**
 * @fileoverview Calendar Javascript
 * @author info@allcreator.net (Allcreator Co.)
 */


/**
 * angularJS, NonANgularJS共通で使う、プラグイン名前空間
 */
var CalendarJS = {};  //専用空間

CalendarJS = {};  //専用空間


/**
 * CalendarJS.variables
 *
 * @type {Object.<string>}
 */
CalendarJS.variables = {
  REPEAT_FREQ_DAILY: 'DAILY',
  REPEAT_FREQ_WEEKLY: 'WEEKLY',
  REPEAT_FREQ_MONTHLY: 'MONTHLY',
  REPEAT_FREQ_YEARLY: 'YEARLY',

  RRULE_TERM_COUNT: 'COUNT',
  RRULE_TERM_UNTIL: 'UNTIL'
};

/**
 * angularJSをつかったJavaScriptプログラム(後半に、"NonAngularJS"コードあり)
 */


/**
 * YYYY-MM形式の年月を、言語別のフォーマットに変形するフィルター
 */
NetCommonsApp.filter('formatYyyymm', function() {
  return function(value, languageId) {
    if (!angular.isString(value)) {  //valueが文字列でなければ加工しない
      return value;
    }
    languageId = (languageId + '') || '2';    //lang指定なければデフォルト言語
    switch (languageId) {  //言語別 YYYY-MM 整形
      case '2':
        value = value.replace(/^(\d{1,4})-(\d{1,2})$/, '$1年$2月');
        break;
      default:
    }
    return value;
  }
});


/**
 * YYYY-MM-DD形式の年月を、言語別のフォーマットに変形するフィルター
 */
NetCommonsApp.filter('formatYyyymmdd', function() {
  return function(value, languageId) {
    if (!angular.isString(value)) {  //valueが文字列でなければ加工しない
      return value;
    }

    languageId = (languageId + '') || '2';    //lang指定なければデフォルト言語
    switch (languageId) {  //言語別 YYYY-MM-DD 整形
      case '2':
        value = value.replace(/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/, '$1年$2月$3日');
        break;
      default:
    }
    return value;
  }
});


/**
 * 予定の編集・削除のモーダル表示サービス
 */
NetCommonsApp.factory('ConfirmRepeat', ['NetCommonsModal',
  function(NetCommonsModal) {
    return function($scope, frameId, action) {
      var actionWord = 'UNKNOWN';
      var actionButton = '';
      if (action == 'edit') {
        actionWord = '変更';
        actionButton =
            "<button name='edit' type='button' ng-click='ok()' " +
            "class='btn btn-primary'>" +
            "<span class='glyphicon glyphicon-edit'></span>編集" +
            '</button>';
      } else if (action == 'delete') {
        actionWord = '削除';
        actionButton =
            "<button name='edit' type='button' ng-click='ok()' " +
            "class='btn btn-danger'>" +
            "<span class='glyphicon glyphicon-trash'></span>" +
            '</button>';
      }
      var tmpl =
          "<form action='#' method='get'>" +
          "<div class='row form-group'>" +
          "<div class='col-xs-12' style='margin:1em 0.5em 0.5em 0.5em; " +
          "text-align:center'>" +
          "<span class='h3'>繰返しタイプの選択</span>" +
          '</div>' +
          "<div class='clearfix'></div>" +
          '<hr />' +
          "<div class='col-xs-11 col-xs-offset-1'>" +
          "<input name= 'selectRepeatType" + frameId + "' type='radio' " +
          "value='1'>この予定のみ" + actionWord + 'する' +
          '</div>' +
          "<div class='clearfix'></div>" +
          "<div class='col-xs-11 col-xs-offset-1'>" +
          "<input name= 'selectRepeatType" + frameId + "' " +
          "type='radio' value='2'>" +
          'この予定以降を' + actionWord + 'する' +
          '</div>' +
          "<div class='clearfix'></div>" +
          "<div class='col-xs-11 col-xs-offset-1'>" +
          "<input name= 'selectRepeatType" + frameId + "' type='radio' " +
          "value='3'>全ての予定を" + actionWord + 'する' +
          '</div>' +
          "<div class='clearfix'></div>" +
          '<hr/>' +
          "<div class='col-xs-12 text-center'>" +
          "<button name='cancel' type='button' ng-click='cancel()' " +
          "class='btn btn-default' style='margin-right:1em'>" +
          "<span class='glyphicon glyphicon-remove'></span>キャンセル" +
          '</button>' +
          actionButton +
          '</div>' +
          '</div><!--form-groupおわり-->' +
          '</form>';

      return NetCommonsModal.show(
          $scope,            //モーダルと呼び元で共有するスコープ変数
          'CalendarModalCtrl',     //'CalendarsDetailEdit',    //controller名
          //$scope.baseUrl + '/faqs/faq_questions/selectrepeat?frame_id=11',
          null,            //url=templateUrlはなし
          {
            template: tmpl,
            backdrop: false //'static'
          }
      );
    }
  }
]);

NetCommonsApp.controller('CalendarSchedule', function($scope) {
  $scope.initialize = function(data) {
    $scope.isCollapsed = data.isCollapsed;
  };
});

NetCommonsApp.controller('CalendarsTimeline', ['$scope', function($scope) {
  console.log('TIMELINE...');

  //タイムラインdiv
  var coordinateOrigins = $('.calendar-daily-timeline-coordinate-origin');

  //指定時間のindex値を、タイムラインdivの属性から取り出し
  var idx = $(coordinateOrigins[0]).attr('data-daily-start-time-idx') - 0;

  //00:00の行のtop 誤差をなくすため2300に変更
  //var row0 = $('.calendar-daily-timeline-0000');
  //var row0Top = row0[0].getBoundingClientRect().top;

  //01:00の行のtop
  var row1 = $('.calendar-daily-timeline-0100');
  var row1Top = row1[0].getBoundingClientRect().top;

  //23:00の行のtop
  var row23 = $('.calendar-daily-timeline-2300');
  var row23Top = row23[0].getBoundingClientRect().top;

  //1行(=１時間)の高さ
  //var rowHeight = row1Top - row0Top;
  var rowHeight = (row23Top - row1Top) / 22;

  //指定時間が最初になるよう、divの縦スクロールを移動
  coordinateOrigins[0].scrollTop = rowHeight * idx;

  //$scope.origin = coordinateOrigins[0].scrollTop;
  $scope.rowHeight = rowHeight;

  //0:00高さ固定
  var dataArea = $('.calendar-timeline-data-area');
  dataArea[0].style.height = String(rowHeight) + 'px'; //固定にしないと伸びてしまう

  var row1Width = row1[0].getBoundingClientRect().width;
  $scope.rowWidth = row1Width;
  console.log('rowWitdh %d', row1Width);

  //初期化
  $scope.prevMargin = 0;
  $scope.maxLineNum = 0;
  $scope.Column = [];
  $scope.Column[0] = [];

}]);

NetCommonsApp.controller('CalendarsTimelinePlan', function($scope) {
  $scope.calendarPlans = [];
  console.log('TIMELINE.Plan!!!..%d', $scope.rowHeight);

  $scope.initialize = function(data) {
    console.log('TIMELINE.Plan!INIT!..');
    $scope.calendarPlans = data.calendarPlans;
    console.log('plan! %d', data.calendarPlans.length);

    //位置情報を設定
    for (var i = 0; i < data.calendarPlans.length;
        i++) {
      $scope.setTimelinePos(i, $scope.calendarPlans[i].
          fromTime, $scope.calendarPlans[i].toTime);
    }
  };

  $scope.setTimelinePos = function(id, fromTime, toTime) {
    console.log('TIMELINE.Plan!setTimelinePos!..');
    var planObj = document.getElementById('plan' + String(id));

    var start = fromTime.split(':');
    var end = toTime.split(':');

    var startHour = parseInt(start[0]);
    var startMin = parseInt(start[1]);

    var endHour = parseInt(end[0]);
    var endMin = parseInt(end[1]);

    if (endHour < startHour) {
      endHour = 24;
    }

    //高さ
    var height = endHour - startHour;
    height = (height + ((endMin - startMin) / 60)) * $scope.rowHeight;

    //開始位置
    var top = (startHour + (startMin / 60)) * $scope.rowHeight;

    //タイムライン重ならない列数を取得
    var lineNum = $scope.getLineNum(top, (height + top));

    //位置決定
    planObj.style.height = String(height) + 'px';
    planObj.style.top = String(top - $scope.prevMargin) + 'px'; //(調整)

    //前回の位置が蓄積されてくる※位置調整のため
    $scope.prevMargin = $scope.prevMargin + height;
    console.log('prevMargin%d', $scope.prevMargin);

    //次回の重なりチェックのため、値保持
    var data = {x: top, y: (height + top)};
    $scope.Column[lineNum].push(data);

    //左からの位置
    planObj.style.left = String((lineNum * ($scope.rowWidth + 15)) + 5) + 'px';
    planObj.style.position = 'relative';
  };

  $scope.getLineNum = function(x, y) {
    console.log('TIMELINE.Plan!getLineNum!..%d %d', x, y);

    //0列目からチェック
    for (var i = 0; i <= $scope.maxLineNum; i++) {
      if ($scope.checkColumn(i, x, y) == false) {
        return i; //重なりの無い列を返却
      }
    }

    $scope.maxLineNum++; //新しい列
    $scope.Column[$scope.maxLineNum] = [];
    return $scope.maxLineNum;
  };

  $scope.checkColumn = function(checkColumn, x, y) {
    console.log('TIMELINE.Plan!checkColumn!..');

    //指定列の重なりチェック
    for (var i = 0; i < $scope.Column[checkColumn].length; i++) {
      if ($scope.checkOverlap($scope.Column[checkColumn][i].
          x, $scope.Column[checkColumn][i].y, x, y) == true) {
        console.log('OVER!! checkColumn %d', i);
        return true;
      }
    }
    return false; //重なりなし
  };

  $scope.checkOverlap = function(x1, y1, x2, y2) {
    console.log('TIMELINE.Plan!checkOverlap!..');

    //線分1と線分2の重なりチェック
    if (x1 >= x2 && x1 >= y2 &&
        y1 >= x2 && y1 >= x2) {
      return false;
    }
    if (x2 >= x1 && x2 >= y1 &&
        y2 >= x1 && y2 >= y1) {
      return false;
    }
    return true; //重なりあり
  };

});


NetCommonsApp.controller('CalendarDetailEditWysiwyg',
    ['$scope', 'NetCommonsWysiwyg', function($scope, NetCommonsWysiwyg) {
      /**
       * tinymce
       *
       * @type {object}
       */
      $scope.tinymce = NetCommonsWysiwyg.new();
    }]
);
NetCommonsApp.controller('CalendarsDetailEdit',
    ['$scope', 'ConfirmRepeat', function($scope, ConfirmRepeat) {
      $scope.repeatArray = [];  //key=Frame.id、value=T/F of checkbox
      //key=Frame.id,value=index number
      //of option elements
      $scope.exposeRoomArray = [];
      //key=Frame.id,value=T/F of checkbox
      $scope.selectRepeatPeriodArray = [];
      $scope.targetYear = '2016';
      $scope.startDate = [];
      $scope.startDatetime = [];
      $scope.useTime = [];
      $scope.monthlyDayOfTheWeek = [];
      $scope.monthlyDate = [];
      $scope.yearlyDayOfTheWeek = [];
      $scope.selectRepeatEndType = [];
      $scope.useNoticeMail = [];

      $scope.detailStartDate;
      $scope.detailStartDatetime;
      $scope.detailEndDate;
      $scope.detailEndDatetime;

      console.log('DEBUGGING...');

      $scope.initialize = function(data) {
        $scope.data = angular.fromJson(data);
      };

      $scope.changeDetailStartDate = function(targetId) {
        //
        if ($scope.detailStartDate != '') {
          $('#' + targetId).val($scope.detailStartDate);

          //簡易では、開始日と終了日が統一され「終日」
          //(実質開始のみ）１つとなった。
          //そのため、開始日の値を、終了日のDOMの値に代入する
          //
          var endTargetId = targetId.replace(/Start/g, 'End');
          $('#' + endTargetId).val($scope.detailStartDate);
    
        }
      };

      $scope.changeDetailStartDatetime = function(targetId) {
        //
        if ($scope.detailStartDatetime != '') {
          $('#' + targetId).val($scope.detailStartDatetime);
          //
        }
      };

      $scope.changeDetailEndDate = function(targetId) {
        //
        if ($scope.detailEndDate != '') {
          $('#' + targetId).val($scope.detailEndDate);
          //
        }
      };

      $scope.changeDetailEndDatetime = function(targetId) {
        //
        if ($scope.detailEndDatetime != '') {
          $('#' + targetId).val($scope.detailEndDatetime);
          //
        }
      };

      $scope.changeYearMonth = function(prototypeUrl) {
        var elms = $scope.targetYear.split('-');
        var url = prototypeUrl.replace('YYYY', elms[0]);
        url = url.replace('MM', elms[1]);
        //console.log('frameId[' + frameId + '] prototypeUrl[' +
        //  prototypeUrl + '] targetYear[' + $scope.targetYear +
        //  '] url[' + url + ']');
        window.location = url;
      };
      $scope.changeYearMonthDay = function(prototypeUrl) {
        //console.log('DEBUGGING...' + $scope.targetYear);

        var elms = $scope.targetYear.split('-');
        var url = prototypeUrl.replace('YYYY', elms[0]);
        url = url.replace('MM', elms[1]);
         url = url.replace('DD', elms[2]);
        //console.log('frameId[' + frameId + '] prototypeUrl[' +
        //  prototypeUrl + '] targetYear[' + $scope.targetYear +
        //  '] url[' + url + ']');
        window.location = url;
      };

      $scope.toggleRepeatArea = function(frameId) {
        var elm = $('.calendar-repeat-a-plan-detail_' + frameId);
        if ($scope.repeatArray[frameId]) {
          elm.show();
        } else {
          elm.hide();
        }
      };

      $scope.changeRoom = function(myself, frameId) {
        var elm = $('.calendar-plan-share_' + frameId);
        if ($scope.exposeRoomArray[frameId].toString() === myself.toString()) {
          //console.log('グループ共有が有効になる');
          elm.show();
        } else {
          //console.log('グループ共有が無効になる');
          elm.hide();
        }
      };

      $scope.setInitRepeatPeriod = function(frameId, idx) {
        //これで、画面をリフレッシュ
        console.log(frameId + '/' + idx);
        $scope.selectRepeatPeriodArray[frameId] = idx;
      };

      $scope.changePeriodType = function(frameId) {
        var elmDaily = $('.calendar-daily-info_' + frameId);
        var elmWeekly = $('.calendar-weekly-info_' + frameId);
        var elmMonthly = $('.calendar-monthly-info_' + frameId);
        var elmYearly = $('.calendar-yearly-info_' + frameId);

        switch ($scope.selectRepeatPeriodArray[frameId]) {
          case CalendarJS.variables.REPEAT_FREQ_DAILY:
            console.log('日単位');
            elmDaily.removeClass('hidden').addClass('show');
            elmWeekly.removeClass('show').addClass('hidden');
            elmMonthly.removeClass('show').addClass('hidden');
            elmYearly.removeClass('show').addClass('hidden');
            break;
          case CalendarJS.variables.REPEAT_FREQ_WEEKLY:
            console.log('週単位');
            elmDaily.removeClass('show').addClass('hidden');
            elmWeekly.removeClass('hidden').addClass('show');
            elmMonthly.removeClass('show').addClass('hidden');
            elmYearly.removeClass('show').addClass('hidden');
            break;
          case CalendarJS.variables.REPEAT_FREQ_MONTHLY:
            console.log('月単位');
            elmDaily.removeClass('show').addClass('hidden');
            elmWeekly.removeClass('show').addClass('hidden');
            elmMonthly.removeClass('hidden').addClass('show');
            elmYearly.removeClass('show').addClass('hidden');
            break;
          case CalendarJS.variables.REPEAT_FREQ_YEARLY:
            console.log('年単位');
            elmDaily.removeClass('show').addClass('hidden');
            elmWeekly.removeClass('show').addClass('hidden');
            elmMonthly.removeClass('show').addClass('hidden');
            elmYearly.removeClass('hidden').addClass('show');
            break;
        }
      };

      $scope.initDescription = function(descriptionVal) {
        $scope.calendarActionPlan = {};
        $scope.calendarActionPlan.description = descriptionVal;
      };

      $scope.toggleEnableTime = function(frameId) {
        console.log('useTime[' + $scope.useTime + ']');
        if ($scope.useTime[frameId]) {
          //時刻なし(YYYY-MM-DD) -> 時刻あり(YYYY-MM-DD HH:mm)
          if ($scope.detailStartDatetime && $scope.detailStartDatetime.indexOf(':') >= 0) {
            //$scopeの方はYYYY-MM-DD HH:mm
            var domVal = $('#CalendarActionPlanDetailStartDatetime').val();
            if (!domVal || domVal.indexOf(':') === (-1)) {
              //DOMの方は YYYY-MM-DD HH:mm「ではない」.未定義かYYYY-MM-DD
              //なので、$scopeの値を、DOMに反映する。
              $('#CalendarActionPlanDetailStartDatetime').val($scope.detailStartDatetime);
            }
          }
          if ($scope.detailEndDatetime && $scope.detailEndDatetime.indexOf(':') >= 0) {
            //$scopeの方はYYYY-MM-DD HH:mm
            var domVal = $('#CalendarActionPlanDetailEndDatetime').val();
            if(!domVal || domVal.indexOf(':') === (-1)) {
              //DOMの方は YYYY-MM-DD HH:mm「ではない」.未定義かYYYY-MM-DD
              //なので、$scopeの値を、DOMに反映する。
              $('#CalendarActionPlanDetailEndDatetime').val($scope.detailEndDatetime);
            }
          }
					console.log('時刻なし(YYYY-MM-DD) -> 時刻あり(YYYY-MM-DD HH:mm)');
        } else {
          //時刻あり(YYYY-MM-DD HH:mm) -> 時刻なし(YYYY-MM-DD)
          if ($scope.detailStartDate && $scope.detailStartDate.indexOf(':') === (-1)) {
            //$scopeの方はYYYY-MM-DD
            var domVal = $('#CalendarActionPlanDetailStartDatetime').val();
            if (!domVal || domVal.indexOf(':') > 0) {
              //DOMの方は YYYY-MM-DD「ではない」.未定義かYYYY-MM-DD HH:mm
              //なので、$scopeの値を、DOMに反映する。
              $('#CalendarActionPlanDetailStartDatetime').val($scope.detailStartDate);
            }
          }
          if ($scope.detailEndDate && $scope.detailEndDate.indexOf(':') === (-1)) {
            //$scopeの方はYYYY-MM-DD
            var domVal = $('#CalendarActionPlanDetailEndDatetime').val();
            if (!domVal || domVal.indexOf(':') > 0) {
              //DOMの方は YYYY-MM-DD「ではない」.未定義かYYYY-MM-DD HH:mm
              //なので、$scopeの値を、DOMに反映する。
              $('#CalendarActionPlanDetailEndDatetime').val($scope.detailEndDate);
            }
          }
          console.log('時刻あり(YYYY-MM-DD HH:mm) -> 時刻なし(YYYY-MM-DD)');

          //checkboxのDOMの値も同期させておく。
          /////$('#CalendarActionPlanEnableTime').prop('checked', true);

        }
        //結果
				console.log('$scope:');
				console.log('sdt[' + $scope.detailStartDatetime + ']');
				console.log('sd [' + $scope.detailStartDate + ']');
				console.log('edt[' + $scope.detailEndDatetime + ']');
				console.log('ed [' + $scope.detailEndDate + ']');
				console.log('DOM:');
				console.log('sdt[' + $('#CalendarActionPlanDetailStartDatetime').val()  + ']');
				console.log('edt[' + $('#CalendarActionPlanDetailEndDatetime').val()  + ']');

        console.log('enable_time[' + $('#CalendarActionPlanEnableTime').prop('checked') + ']');
      };

      $scope.changeMonthlyDayOfTheWeek = function(frameId) {
        if ($scope.monthlyDayOfTheWeek[frameId] !== '') {
          $scope.monthlyDate[frameId] = '';
        }
      };

      $scope.changeMonthlyDate = function(frameId) {
        if ($scope.monthlyDate[frameId] !== '') {
          $scope.monthlyDayOfTheWeek[frameId] = '';
        }
      };

      $scope.changeYearlyDayOfTheWeek = function(frameId) {
        //yearlyの方は、monthlyと違いDateの方がない.つまり
        //DayOfTheWeekとDateをトグルする必要がないので、なにもしない。
      };

      $scope.setInitRepeatEndType = function(frameId, idx) {
        $scope.selectRepeatEndType[frameId] = idx;  //画面をリフレッシュ
      };

      $scope.changeRepeatEndType = function(frameId) {
        var elmCount = $('.calendar-repeat-end-count-info_' + frameId);
        var elmEndDate = $('.calendar-repeat-end-enddate-info_' + frameId);

        switch ($scope.selectRepeatEndType[frameId]) {
          case CalendarJS.variables.RRULE_TERM_COUNT:
            console.log('回数指定');
            elmCount.removeClass('hidden').addClass('show');
            elmEndDate.removeClass('show').addClass('hidden');
            break;
          case CalendarJS.variables.RRULE_TERM_UNTIL:
            console.log('終了日指定');
            elmCount.removeClass('show').addClass('hidden');
            elmEndDate.removeClass('hidden').addClass('show');
            break;
        }
      };

      $scope.showRepeatConfirm = function(frameId, isRepeat, action) {
        if (isRepeat === 'On') {
          console.log('繰返し処理');
          var modalInstance = ConfirmRepeat($scope, frameId, action);
          modalInstance.result.then(function(result) {
            console.log('成功 case');
          },
          function() {
            console.log('失敗 case');
          });
          return;
        }

        //繰返しなし
        if (action === 'edit') {
          console.log('編集画面へ遷移');
          return;
        }

        if (action === 'delete') {
          if (confirm('delete ok')) {
            //
            //予定の削除処理をpostします。
            //
            console.log('１件削除処理実行');
          }
          return;
        }
      };

      $scope.setInitNoticeMailSetting = function(frameId, bVal) {
        $scope.useNoticeMail[frameId] = bVal;  //画面をリフレッシュ
      };

      $scope.toggleNoticeMailSetting = function(frameId) {
        if ($scope.useNoticeMail[frameId]) {
          //メール通知を使用する
          $('.calendar-mail-setting_' + frameId).show();
        } else {
          //メール通知を使用しない
          $('.calendar-mail-setting_' + frameId).hide();
        }
      };
    }]
);

NetCommonsApp.controller('CalendarModalCtrl', [
  '$scope', '$modalInstance', function($scope, $modalInstance) {
    $scope.ok = function() {
      console.log('ok clicked');
      $modalInstance.close();
    };
    $scope.cancel = function() {
      console.log('candel clicked');
      $modalInstance.dismiss();
    };
  }
]);


/**
 * CalendarFrameSettings Javascript
 *
 * @param {string} Controller name
 * @param {function($scope)} Controller
 */
NetCommonsApp.controller('CalendarFrameSettings', [
  '$scope', function($scope) {
    /**
     * variables
     *
     * @type {Object.<string>}
     */
    var variables = {
      CALENDAR_DISP_TYPE_SMALL_MONTHLY: '1',
      CALENDAR_DISP_TYPE_LARGE_MONTHLY: '2',
      CALENDAR_DISP_TYPE_WEEKLY: '3',
      CALENDAR_DISP_TYPE_DAILY: '4',
      CALENDAR_DISP_TYPE_TSCHEDULE: '5',
      CALENDAR_DISP_TYPE_MSCHEDULE: '6'
    };

    $scope.initialize = function(data) {
      $scope.data = angular.fromJson(data);
      //$scope.data.frameId;
      //$scope.data.calendarFrameSetting
      //$scope.data.calendarFrameSettingSelectRoom
      //$scope.data.displayTypeOptions
      //が格納される。

      $scope.displayTypes = [];

      $scope.setIsShowElement();

      angular.forEach($scope.data.displayTypeOptions, function(val, key, obj) {
        $scope.displayTypes.push({
          label: val,
          value: key
        });
      });
    };

    $scope.displayChange = function() {
      $scope.setIsShowElement();
    };
    /**
    * 各種選択要素を出してよいかどうか
    */
    $scope.setIsShowElement = function() {
      var type = $scope.data.calendarFrameSetting.displayType;
      if (type == variables.CALENDAR_DISP_TYPE_SMALL_MONTHLY ||
          type == variables.CALENDAR_DISP_TYPE_LARGE_MONTHLY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = false;
      } else if (type == variables.CALENDAR_DISP_TYPE_WEEKLY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = true;
        $scope.isShowTimelineStart = false;
      } else if (type == variables.CALENDAR_DISP_TYPE_DAILY) {
        $scope.isShowStartPos = false;
        $scope.isShowDisplayCount = false;
        $scope.isShowTimelineStart = true;
      } else if (type == variables.CALENDAR_DISP_TYPE_TSCHEDULE ||
          type == variables.CALENDAR_DISP_TYPE_MSCHEDULE) {
        $scope.isShowStartPos = true;
        $scope.isShowDisplayCount = true;
        $scope.isShowTimelineStart = false;
      } else {
        $scope.isShowStartPos = true;
        $scope.isShowDisplayCount = true;
        $scope.isShowTimelineStart = true;
      }
    };
  }]);


/**
 * angularJSに依存しないJavaScriptプログラム(NonAngularJS)
 */
//var CalendarJS = {};  //専用空間

$(function() {
  var expr = '.calendar-col-week, .calendar-easy-edit,';
  expr += ' .calendar-daily-disp, .calendar-detail-edit';
  $(expr).on('click', function(evt) {
    window.location = $(evt.target).attr('data-url');
  });
  $('.calendar-plan-list').on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    if (url == undefined) {
      url = $(evt.target).parents('td.calendar-plan-list').attr('data-url');
    }
    window.location = url;
  });
  $('.calendar-easy-edit-area').on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    if (url == undefined) {
      var expr = 'div.calendar-easy-edit-area';
      url = $(evt.target).parents(expr).attr('data-url');
    }
    window.location = url;
  });
  $('.calendar-plan-show').on('click', function(evt) {
    var url = $(evt.target).attr('data-url');
    if (url == undefined) {
      var expr = 'p.calendar-plan-show';
      url = $(evt.target).parents(expr).attr('data-url');
    }
    window.location = url;
  });
  $('.select-expose-target').on('change', function(evt) {
    //console.log("selectExposeTarget change");
    var myself = $('.select-expose-target').attr('data-myself');
    var frameId = $('.select-expose-target').attr('data-frame-id');
    var elm = $('.calendar-plan-share_' + frameId);
    if ($('.select-expose-target option:selected').val() == myself) {
      //console.log('グループ共有が有効になる');
      elm.show();
    } else {
      //console.log('グループ共有が無効になる');
      elm.hide();
    }
  });
});

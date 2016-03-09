/**
 * @fileoverview Calendar Javascript
 * @author info@allcreator.net (Allcreator Co.)
 */


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

NetCommonsApp.controller('CalendarsTimeline', ['$scope', function($scope) {
  console.log('TIMELINE...');

  //タイムラインdiv
  var coordinateOrigins = $('.calendar-daily-timeline-coordinate-origin');

  //指定時間のindex値を、タイムラインdivの属性から取り出し
  var idx = $(coordinateOrigins[0]).attr('data-daily-start-time-idx') - 0;

  //00:00の行のtop
  var row0 = $('.calendar-daily-timeline-0000');
  var row0Top = row0[0].getBoundingClientRect().top;

  //01:00の行のtop
  var row1 = $('.calendar-daily-timeline-0100');
  var row1Top = row1[0].getBoundingClientRect().top;

  //1行(=１時間)の高さ
  var rowHeight = row1Top - row0Top;

  //指定時間が最初になるよう、divの縦スクロールを移動
  coordinateOrigins[0].scrollTop = rowHeight * idx;

}]);

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
      $scope.selectRepeatEndType = [];
      $scope.useNoticeMail = [];

      console.log('DEBUGGING...');

      $scope.changeYearMonth = function(prototypeUrl) {
        var elms = $scope.targetYear.split('-');
        var url = prototypeUrl.replace('YYYY', elms[0]);
        url = url.replace('MM', elms[1]);
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
        $scope.selectRepeatPeriodArray[frameId] = idx;
      };

      $scope.changePeriodType = function(frameId) {
        var elmDaily = $('.calendar-daily-info_' + frameId);
        var elmWeekly = $('.calendar-weekly-info_' + frameId);
        var elmMonthly = $('.calendar-monthly-info_' + frameId);
        var elmYearly = $('.calendar-yearly-info_' + frameId);

        switch ($scope.selectRepeatPeriodArray[frameId]) {
          case '0':
            console.log('日単位');
            elmDaily.removeClass('hidden').addClass('show');
            elmWeekly.removeClass('show').addClass('hidden');
            elmMonthly.removeClass('show').addClass('hidden');
            elmYearly.removeClass('show').addClass('hidden');
            break;
          case '1':
            console.log('週単位');
            elmDaily.removeClass('show').addClass('hidden');
            elmWeekly.removeClass('hidden').addClass('show');
            elmMonthly.removeClass('show').addClass('hidden');
            elmYearly.removeClass('show').addClass('hidden');
            break;
          case '2':
            console.log('月単位');
            elmDaily.removeClass('show').addClass('hidden');
            elmWeekly.removeClass('show').addClass('hidden');
            elmMonthly.removeClass('hidden').addClass('show');
            elmYearly.removeClass('show').addClass('hidden');
            break;
          case '3':
            console.log('年単位');
            elmDaily.removeClass('show').addClass('hidden');
            elmWeekly.removeClass('show').addClass('hidden');
            elmMonthly.removeClass('show').addClass('hidden');
            elmYearly.removeClass('hidden').addClass('show');
            break;
        }
      };

      $scope.toggleEnableTime = function(frameId) {
        console.log('useTime[' + $scope.useTime + ']');
      };

      $scope.changeMonthyDayOfTheWeek = function(frameId) {
        if ($scope.monthlyDayOfTheWeek[frameId] !== '') {
          $scope.monthlyDate[frameId] = '';
        }
      };

      $scope.changeMonthlyDate = function(frameId) {
        if ($scope.monthlyDate[frameId] !== '') {
          $scope.monthlyDayOfTheWeek[frameId] = '';
        }
      };

      $scope.setInitRepeatEndType = function(frameId, idx) {
        $scope.selectRepeatEndType[frameId] = idx;  //画面をリフレッシュ
      };

      $scope.changeRepeatEndType = function(frameId) {
        var elmCount = $('.calendar-repeat-end-count-info_' + frameId);
        var elmEndDate = $('.calendar-repeat-end-enddate-info_' + frameId);

        switch ($scope.selectRepeatEndType[frameId]) {
          case '0':
            console.log('回数指定');
            elmCount.removeClass('hidden').addClass('show');
            elmEndDate.removeClass('show').addClass('hidden');
            break;
          case '1':
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
    $scope.initialize = function(data) {
      $scope.data = angular.fromJson(data);

      //$scope.data.frameId;
      //$scope.data.calendarFrameSetting
      //$scope.data.displayTypeOptions
      //が格納されている。

      $scope.displayTypes = [];

      angular.forEach($scope.data.displayTypeOptions, function(val, key, obj) {
        //console.log('key[' + key + '] val[' + val + ']');
        $scope.displayTypes.push({
          label: val,
          value: key
        });
      });
    };

    $scope.displayChange = function(frameId) {
      console.log('change');
    };
  }
]);


/**
 * angularJSに依存しないJavaScriptプログラム(NonAngularJS)
 */
var CalendarFrameSettingJS = {};  //専用空間


/**
 * 表示方法の変更
 *
 * @param {string} id ID文字列
 * @param {string} frameId フレームID
 */
CalendarFrameSettingJS.changeDispType = function(id, frameId) {
  var jqObj = $('#' + id);
  if (jqObj.attr('data-calendar-frame-id') != frameId) {
    console.log('jqObj frameId[' + jqObj.attr('dataCalendarFrameId') +
        '] and frameId[' + frameId + '] mismatch!');
    return;
  }

  var display_type = $('#' + id + ' option:selected').attr('value');
  if (display_type == CalendarsCommon.DISP_TYPE_SMALL_MONTHLY ||
      display_type == CalendarsCommon.DISP_TYPE_LARGE_MONTHLY) {
    $('div[name=kaishiIchi]').addClass('calendar-hide');
    $('div[name=numOfDays]').addClass('calendar-hide');
    $('div[name=startHourOfTimeline]').addClass('calendar-hide');
  } else if (display_type == CalendarsCommon.DISP_TYPE_WEEKLY) {
    $('div[name=kaishiIchi]').addClass('calendar-hide');
    $('div[name=numOfDays]').removeClass('calendar-hide');
    $('div[name=startHourOfTimeline]').addClass('calendar-hide');
  } else if (display_type == CalendarsCommon.DISP_TYPE_DAILY) {
    $('div[name=kaishiIchi]').addClass('calendar-hide');
    $('div[name=numOfDays]').addClass('calendar-hide');
    $('div[name=startHourOfTimeline]').removeClass('calendar-hide');
  } else if (display_type == CalendarsCommon.DISP_TYPE_TSCHEDULE ||
      display_type == CalendarsCommon.DISP_TYPE_MSCHEDULE) {
    $('div[name=kaishiIchi]').removeClass('calendar-hide');
    $('div[name=numOfDays]').removeClass('calendar-hide');
    $('div[name=startHourOfTimeline]').addClass('calendar-hide');
  }
};


/**
 * ルーム選択の変更
 *
 * @param {string} id ID文字列
 * @param {string} frameId フレームID
 */
CalendarFrameSettingJS.changeIsSelectRoom = function(id, frameId) {
  var jqObj = $('#' + id);
  if (jqObj.attr('data-calendar-frame-id') != frameId) {
    console.log('jqObj frameId[' + jqObj.attr('dataCalendarFrameId') +
        '] and frameId[' + frameId + '] mismatch!');
    return;
  }

  var checked = $('#' + id).prop('checked');
  if (checked) {
    $('div[name=roomSelect]').removeClass('calendar-hide');
  } else {
    $('div[name=roomSelect]').addClass('calendar-hide');
  }
};


var CalendarJS = {};  //専用空間

$(function() {
  $('.calendar-col-week').on('click', function(evt) {
    window.location = $(evt.target).attr('data-url');
  });
  $('.calendar-easy-edit').on('click', function(evt) {
    window.location = $(evt.target).attr('data-url');
  });
});


<?php
?>
<table class="table table-hover">
  <caption>記事の投稿権限と承認の要否</caption>
  <thead>
    <tr>
      <th class="text-center">ルーム名</th>
      <th class="text-center">ルーム管理者</th>
      <th class="text-center">編集長</th>
      <th class="text-center">編集者</th>
      <th class="text-center">一般</th>
      <th class="text-center">承認が必要</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>パブリックスペース</td>
      <td class="text-center"><input type="checkbox" name="roomAdm" value="1" checked disabled /></td>
      <td class="text-center"><input type="checkbox" name="cheifeditor" value="2" /></td>
      <td class="text-center"><input type="checkbox" name="editor" value="3" /></td>
      <td class="text-center"><input type="checkbox" name="general" value="4" /></td>
      <td class="text-center"><input type="checkbox" name="approval" value="5" /></td>
    </tr>
    <tr>
      <td>ルームA</td>
      <td class="text-center"><input type="checkbox" name="roomAdm" value="1" checked disabled /></td>
      <td class="text-center"><input type="checkbox" name="cheifeditor" value="2" /></td>
      <td class="text-center"><input type="checkbox" name="editor" value="3" /></td>
      <td class="text-center"><input type="checkbox" name="general" value="4" /></td>
      <td class="text-center"><input type="checkbox" name="approval" value="5" /></td>
    </tr>
    <tr>
      <td>ルームB</td>
      <td class="text-center"><input type="checkbox" name="roomAdm" value="1" checked disabled /></td>
      <td class="text-center"><input type="checkbox" name="cheifeditor" value="2" /></td>
      <td class="text-center"><input type="checkbox" name="editor" value="3" /></td>
      <td class="text-center"><input type="checkbox" name="general" value="4" /></td>
      <td class="text-center"><input type="checkbox" name="approval" value="5" /></td>
    </tr>
    <tr>
      <td>全会員</td>
      <td class="text-center"><input type="checkbox" name="roomAdm" value="1" /></td>
      <td class="text-center"><input type="checkbox" name="cheifeditor" value="2" /></td>
      <td class="text-center"><input type="checkbox" name="editor" value="3" /></td>
      <td class="text-center"><input type="checkbox" name="general" value="4" /></td>
      <td class="text-center"><input type="checkbox" name="approval" value="5" /></td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <th class="text-center">ルーム名</th>
      <th class="text-center">ルーム管理者</th>
      <th class="text-center">編集長</th>
      <th class="text-center">編集者</th>
      <th class="text-center">一般</th>
      <th class="text-center">承認が必要</th>
    </tr>
  </tfoot>
</table>
<br />

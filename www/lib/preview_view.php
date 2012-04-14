<?php
function h($str){
  return htmlspecialchars($str);
}
function kafoof_section($s){
  ?>
<div style="margin-bottom:10px; border:solid 1px #ccc; -moz-border-radius:4px; border-radius:4px;">
<table cellpadding="0" cellspacing="0" border="0" style="font-family:inherit;width:100%; max-width:900px;">
  <? if(@$s->title): ?>
    <tr><td colspan="3"><h3 style="font-family:inherit;margin:0;padding:0;"><?=h($s->title)?></h3></td></tr>
    <tr><td colspan="3" style="font-family:inherit;height:7px; line-height:7px; font-size:5px;">&nbsp;</td></tr>
  <? else: ?>
    <tr><td colspan="3" style="font-family:inherit;height:10px; line-height:10px; font-size:5px;">&nbsp;</td></tr>
  <? endif; ?>
  <tr>
    <td style="font-family:inherit;width:10px">&nbsp;</td>
    <td>
  <? foreach($s->rows as $row): ?>
    <table cellpadding="0" cellspacing="0" border="0" style="font-family:inherit;width:100%;">
      <thead>
        <tr>
          <? foreach($row as $f): ?>
            <? if('text'==@$f->type): ?>
              <td rowspan="2" style="font-family:inherit;font-weight:normal; text-align:left; vertical-align:top;">
                <?=h(@$f->value)?>
              </td>
            <? else: ?>
              <th colspan="<?=empty($f->pad) ? 1: 2 ?>" style="font-family:inherit;text-align:left; vertical-align:top; font-weight:normal">
                <?=h(@$f->label)?>
              </th>
            <? endif; ?>
          <? endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <? $cols = 0 ?>
        <tr>
          <? foreach($row as $f): ?>
            <? $cols++; ?>
            <? if('text'==@$f->type): ?>
            <? elseif('field'==@$f->type): ?>
              <td style="font-family:inherit;text-align:left; vertical-align:top; font-weight:normal; color:#00438a;width:<?=h(@$f->width)?>;border-bottom:dotted 1px #aaa;">
                <?=empty($f->value) ? '&nbsp;' : nl2br(h($f->value)) ?>
              </td>
              <? if(!empty($f->pad)): ?>
                <? $cols++; ?>
                <td style="font-family:inherit;width:<?=$f->pad?>"></td>
              <? endif; ?>
            <? elseif('include'==@$f->type): ?>
              <td style="font-family:inherit;vertical-align:top">
                <? foreach(@$f->sets as $sub) kafoof_section($sub); ?>
              </td>
            <? endif; ?>
          <? endforeach; ?>
        </tr>
        <tr><td colspan="<?=$cols?>" style="font-family:inherit;height:10px; line-height:10px; font-size:5px;">&nbsp;</td></tr>
      </tbody>
    </table>
  <? endforeach; ?>
    </td>
    <td style="font-family:inherit;width:10px">&nbsp;</td>
  </tr>
</table>
</div>
  <?php
}
?>
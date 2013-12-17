<!DOCTYPE html><html><head><style>.block{width:100%; height:100%; overflow-y: scroll;}.req{background-color: #cb4042}.res{background-color: #bec23f}.log{background-color: black; color: white}.chain{} html, body, table {width:100%;height:100%;margin:0} table{height:50%;border-collapse:collapse;} td {height:50%;border:none;padding:5px;}</style></head><body><table><tr>
<td class="req" style="width:50%"><div class="block"><code><pre>
<?=$r_request?>
</pre></code></div></td><td class="res"><div class="block"><code><pre>
<?=$r_response?>
</pre></code></div></td></tr></table><table><tr><td class="log" style="width:70%"><div class="block"><code><pre>
<?=$r_log?>
</pre></code></div></td><td class="chain"><div class="block"><code><pre>
<?=$r_chain?>
</pre></code></div></td></tr></table></body></html>
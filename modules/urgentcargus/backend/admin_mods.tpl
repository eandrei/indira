<script>
var urgentcargus_url = '{$smarty.const.__PS_BASE_URI__}';
var adm = '{$smarty.const._PS_ADMIN_DIR_}';
var arr = adm.split('/');
var urgentcargus_admindir = arr.slice(-1).pop();
var secret = '{$smarty.const._COOKIE_KEY_}';
</script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/urgentcargus/backend/admin_mods.js"></script>
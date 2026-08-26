/**
 * Name  ：JS快捷方法V1.0.0
 * Author：星河
 */
/**
 * 检查某成员是否存在与某数组中 模拟PHP的in_array方法 
 */
window.in_array = function ($_var, $_arr) {
    $_in = false;
    if ('object' == typeof ((new Array()).forEach)) {
        $_arr.forEach(function (value) {
            if ($_var == value) {
                $_in = true;
            }
        });
    } else {
        for (var i in $_arr) {
            if ($_arr[i] == $_var) {
                $_in = true;
                break;
            }
        }
    }
    return $_in;
}
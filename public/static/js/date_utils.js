function getNowFormatDate2(val) {
    let date = new Date();
    date = new Date(date.getTime() + val + 10 * 60 * 1000);
    let seperator1 = "-";
    let seperator2 = ":";
    let month = date.getMonth() + 1;
    let strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }

    let hour = date.getHours();
    let minute = date.getMinutes();
    let second = date.getSeconds();

    let currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate + " ";
    if (hour < 10) currentdate += '0';
    currentdate += hour + seperator2;
    if (minute < 10) currentdate += '00' + seperator2;
    else currentdate += parseInt(minute/10) + '0' + seperator2;
    currentdate += '00';
    return currentdate;
}
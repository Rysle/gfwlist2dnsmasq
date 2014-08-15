logger "===== Update GFWList Script Begin ====="
url_dnsmasq_conf_encoded="http://lab.linruizhao.com/list/?get=1"
url_dnsmasq_conf_md5="http://lab.linruizhao.com/list/?md5=1"
file_dnsmasq_conf_encoded="/tmp/dnsmasq.conf.encoded"
file_dnsmasq_conf="/tmp/dnsmasq.conf"
file_dnsmasq_conf_md5="/tmp/dnsmasq.conf.md5"
target_dnsmasq_conf="/etc/dnsmasq.conf"

flag_ok="/tmp/dnsmasq.update.OK"
flag_failed="/tmp/dnsmasq.update.FAILED"

wget $url_dnsmasq_conf_encoded -O $file_dnsmasq_conf_encoded
wget $url_dnsmasq_conf_md5 -O $file_dnsmasq_conf_md5
base64 -d $file_dnsmasq_conf_encoded > $file_dnsmasq_conf

md5_remote=`cat $file_dnsmasq_conf_md5 | awk '{print $1}'`
md5_local=`md5sum $file_dnsmasq_conf | awk '{print $1}'`
echo == remote md5: $md5_remote
echo == local md5: $md5_local
if [ "$md5_local" == "$md5_remote" ]; then
    echo == md5 checked: OK!
    logger "== md5 checked: OK!"
    rm $flag_failed
    touch $flag_ok
    mv $file_dnsmasq_conf $target_dnsmasq_conf
    /etc/init.d/dnsmasq restart
else
    echo == md5 checked: FAILED!
    logger "== md5 checked: FAILED!"
    rm $flag_ok
    touch $flag_failed
fi
logger "===== Update GFWList Script End ====="

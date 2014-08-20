debug() {
    echo "$1"
    logger "$1"
}

debug "===== Update GFWList Script Begin ====="

url_host_root="http://yourdomain.com/yourpath/"
url_dnsmasq_conf_encoded="$url_host_root?get=1"
url_dnsmasq_conf_md5="$url_host_root?md5=1"
file_dnsmasq_conf_encoded="/tmp/dnsmasq.conf.encoded"
file_dnsmasq_conf="/tmp/dnsmasq.conf"
file_dnsmasq_conf_md5="/tmp/dnsmasq.conf.md5"
target_dnsmasq_conf="/etc/dnsmasq.conf"

flag_ok="/tmp/dnsmasq.update.OK"
flag_failed="/tmp/dnsmasq.update.FAILED"

md5_current=$(md5sum $target_dnsmasq_conf | awk '{print $1}')
debug "== current md5: $md5_current"
param_clientmd5="&clientmd5=$md5_current"

if [ "$1" == "1" ]; then
    param_force="&force=1"
    debug "== force update"
else
    param_force=""
    debug "== normal update"
fi

wget "$url_dnsmasq_conf_encoded$param_clientmd5$param_force" -O "$file_dnsmasq_conf_encoded"
wget "$url_dnsmasq_conf_md5" -O "$file_dnsmasq_conf_md5"
base64 -d $file_dnsmasq_conf_encoded > $file_dnsmasq_conf

md5_remote=$(awk '{print $1}' $file_dnsmasq_conf_md5)
md5_local=$(md5sum $file_dnsmasq_conf | awk '{print $1}')
debug "== remote md5: $md5_remote"
debug "== local md5: $md5_local"

if [ "$md5_local" == "$md5_remote" ]; then
    debug "== md5 checked: OK!"
    if [ -e $flag_failed ]; then
        rm $flag_failed
    fi
    touch $flag_ok
    if [ "$md5_local" != "$md5_current" ]; then
        debug "== has update! restart dnsmasq..."
        mv $file_dnsmasq_conf $target_dnsmasq_conf
        /etc/init.d/dnsmasq restart
    else
        debug "== has no update. wait for next time..."
    fi

else
    debug "== md5 checked: FAILED!"
    if [ -e $flag_ok ]; then
        rm $flag_ok
    fi
    touch $flag_failed
fi

debug "===== Update GFWList Script End ====="

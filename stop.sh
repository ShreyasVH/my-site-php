#if lsof -i:$PORT > /dev/null; then
#    echo "Stopping"
#    kill -9 $(lsof -i:$PORT -t)
#fi

if lsof -i:$PORT > /dev/null; then
    echo "Stopping"
    kill -QUIT $(cat php-fpm.pid)
fi
# Script used to make a new chamber folder

# Prompt for a reply of whether to continue or not
prompt(){
    continue="k"
    while [ $continue != "y" ] && [ $continue != "n" ];do
	read -n 1 continue
	echo -en "\b"
    done
    if [ $continue == "y" ];then
	echo -e "yes\n"
    else
	echo -e "no\nEXITING. Aborted by user! \n"
	exit 2
    fi
}


if [ $# -ne 1 ]; then
    echo "This script needs exactly one argument, the folder name for the new chamber, to proceed"
    exit 30
fi

echo "Make new folder named: $1"
echo "Ok to preceed (y/n)?"
mkdir $1
prompt

cd $1
echo "Current folder is: "`pwd`
echo "Ready to make links (y/n)?"
prompt

echo "Linking python files"
ln -s ../sym-files2/*.py .
if [ $? -eq 0 ]; then
    echo -e "...OK\n\n"
else
    echo "Something went wrong with the links. Exiting!"
    exit 12
fi

echo "Linking php files"
ln -s ../sym-files2/*.php .
if [ $? -eq 0 ]; then
    echo -e "...OK\n\n"
else
    echo "Something went wrong with the links. Exiting!"
    exit 12
fi

echo "Linking dygraph"
ln -s ../sym-files2/dygraph .
if [ $? -eq 0 ]; then
    echo -e "...OK\n\n"
else
    echo "Something went wrong with the links. Exiting!"
    exit 12
fi

echo "Linking dygraphs"
ln -s ../sym-files2/dygraphs
if [ $? -eq 0 ]; then
    echo -e "...OK\n\n"
else
    echo "Something went wrong with the links. Exiting!"
    exit 12
fi
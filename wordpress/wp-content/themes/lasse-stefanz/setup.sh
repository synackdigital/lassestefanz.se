#!/usr/bin/env bash


# Let's go! From now on, this shall be our theme's name
while :
do
    read -p "Theme name: " theme_name
    
    if [ -n "$theme_name" ] ; then
        break
    fi
done

# Work out default slug and prefix from theme name
name_components=`echo $theme_name | tr '[:upper:]' '[:lower:]'`
theme_slug=`echo $name_components | sed 's/  */\-/g'`
theme_prefix=$( set -f; printf "%c" $name_components )

# If we have only one word (and sufficently long name), chose first two letters
if test `echo ${#theme_prefix}` -lt 2 && test `echo ${#name_components}` -gt 1 ; then
    theme_prefix=${name_components:0:2}
fi

# Ask user for slug (defaulting to previously computed slug)
read -p "Theme slug [$theme_slug]: " theme_slug_user
if test `echo ${#theme_slug_user}` -gt 0 ; then
    theme_slug=$theme_slug_user
fi

if [ -d "../$theme_slug" ] ; then
    echo "Directory exists ../$theme_slug, choose another name or rename existing directory." 1>&2

    exit 1
fi

if [ -f "../$theme_slug" ] ; then
    echo "File exists ../$theme_slug, choose another name or rename existing file." 1>&2

    exit 1
fi




# Ask user for prefix (defaulting to previously computed prefix)
read -p "Theme prefix [$theme_prefix]: " theme_prefix_user
if test `echo ${#theme_prefix_user}` -gt 0 ; then
    theme_prefix=$theme_prefix_user
fi

# Ask user for author information, defaulting to username
author_name=`eval whoami`
read -p "Author name [$author_name]: " author_name_user
if test `echo ${#author_name_user}` -gt 0 ; then
    author_name=$author_name_user
fi

while :
do
    read -p "Enter Author Email: " author_email
    
    if [ -n "$author_email" ] ; then
        break
    fi
done


while :
do
    read -p "Enter Author URI: " author_uri
    
    if [ -n "$author_uri" ] ; then
        break
    fi
done

# Pad URI with leading http:// if no protocol is given
if [ ${author_uri:0:4} != "http" ] ; then
    author_uri=`echo http://$author_uri`
fi

# Ask user for a theme description (and fall back to the standard generation above)
description="A nice WordPress theme by <a href="$author_uri">$author_name</a> built on Hobo Theme"
read -p "Theme description: " description_user
if test `echo ${#description_user}` -gt 0 ; then
    description=$description_user
fi


# Ask to use grunt or not
use_grunt=true
while true; do
    read -p "Use grunt? [Y/n]: " yn

    if [[ "$yn" == '' ]] ; then
        break;
    fi

    case $yn in
        [Yy]* ) use_grunt=true; break;;
        [Nn]* ) use_grunt=false; break;;
        * ) echo "Please answer yes or no.";;
    esac
done


echo ""
echo "Performing search/replace on template theme"

# Iterate files to replace some of our keys
for file in *.php */*.php *.css languages/*.po
do
    theme_name=`echo "${theme_name}" | sed 's#\/#\\/#'`
    theme_slug=`echo "${theme_slug}" | sed 's#\/#\\/#'`
    theme_prefix=`echo "${theme_prefix}" | sed 's#\/#\\/#'`
    author_name=`echo "${author_name}" | sed 's#\/#\\/#'`
    author_uri=`echo "${author_uri}" | sed 's#\/#\\/#'`
    author_email=`echo "${author_email}" | sed 's#\/#\\/#'`
    description=`echo "${description}" | sed 's#\/#\\/#'`
    
    data=`cat $file`
    data=`echo "$data" | sed -e "s#\%theme_name\%#$theme_name#"`
    data=`echo "$data" | sed -e "s#\%theme_slug\%#$theme_slug#"`
    data=`echo "$data" | sed -e "s#\%theme_prefix\%#$theme_prefix#"`
    data=`echo "$data" | sed -e "s#\%author_name\%#$author_name#"`
    data=`echo "$data" | sed -e "s#\%author_uri\%#$author_uri#"`
    data=`echo "$data" | sed -e "s#\%author_email\%#$author_email#"`
    data=`echo "$data" | sed -e "s#\%description\%#$description#"`
    
    echo "$data" > "$file"
done

echo ""
echo "Renaming directory"

dirname=`basename \`pwd\``

cd ..
mv "$dirname" "$theme_slug"
cd "$theme_slug"


echo ""
echo "Setting up grunt"

if [ $use_grunt == true ] ; then

    mv "config-grunt.rb" "config.rb"
    mv "sass" "assets/sass"

    sed -i .bak 's|\.\.\/\.\.\/|\.\.\/.\.\/\.\.\/|g' "assets/sass/screen.scss"

    rm "assets/sass/screen.scss.bak"
    
    npm install grunt-coffee
    npm install grunt-compass
else
    echo "Not using grunt, deleting grunt.js and assets"

    rm "grunt.js"
    rm "config-grunt.rb"

    rm -rf "assets"
fi


echo ""
echo "Setting up git"

# Remove git directory
rm -rf .git

git_path=`which git`

# If we have git, init new git repo
if [ -n "$git_path" ] ; then
    eval "$git_path init"

    eval "$git_path config branch.master.remote origin"
    eval "$git_path config branch.master.merge refs/heads/master"

    eval "$git_path add -f * */*"
    
    sass_lib_path="sass/lib"
    if [ $use_grunt == true ] ; then
        sass_lib_path="assets/sass/lib"
    fi
    eval "$git_path submodule add -f git@kontoret.sodraesplanaden.se:scss $sass_lib_path"
    
    eval "$git_path commit -am 'First commit'"
    #eval "$git_path status"
    echo ""
    
else
    echo ""
    echo "Error: git is not installed"
    
    exit 1
fi

exit 0
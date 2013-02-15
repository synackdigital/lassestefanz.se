# Require any additional compass plugins here.

#require "rgbapng"          # sudo gem install compass-rgbapng
#require "fancy-buttons"    # compass install -r fancy-buttons -f fancy-buttons (https://github.com/imathis/fancy-buttons)

development = false
environment = (development) ? :development : :production

# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "css"
sass_dir = "assets/sass"
images_dir = "images"
javascripts_dir = "assets/js"
fonts_dir = "fonts"

output_style = (environment == :production) ? :compressed : :expanded

# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

# Let's make precision() give us all the precision that we need
Sass::Script::Number.precision = 10

# To disable debugging comments that display the original location of your selectors. Uncomment:
line_comments = development


# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass

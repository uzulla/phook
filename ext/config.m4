dnl config.m4 for extension phook

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary.

dnl If your extension references something external, use 'with':

dnl PHP_ARG_WITH([phook],
dnl   [for phook support],
dnl   [AS_HELP_STRING([--with-phook],
dnl     [Include phook support])])

dnl Otherwise use 'enable':

PHP_ARG_ENABLE([phook],
  [whether to enable phook support],
  [AS_HELP_STRING([--enable-phook],
    [Enable phook support])],
  [no])

if test "$PHP_PHOOK" != "no"; then
  dnl Write more examples of tests here...

  dnl Remove this code block if the library does not support pkg-config.
  dnl PKG_CHECK_MODULES([LIBFOO], [foo])
  dnl PHP_EVAL_INCLINE($LIBFOO_CFLAGS)
  dnl PHP_EVAL_LIBLINE($LIBFOO_LIBS, PHOOK_SHARED_LIBADD)

  dnl If you need to check for a particular library version using PKG_CHECK_MODULES,
  dnl you can use comparison operators. For example:
  dnl PKG_CHECK_MODULES([LIBFOO], [foo >= 1.2.3])
  dnl PKG_CHECK_MODULES([LIBFOO], [foo < 3.4])
  dnl PKG_CHECK_MODULES([LIBFOO], [foo = 1.2.3])

  dnl Remove this code block if the library supports pkg-config.
  dnl --with-phook -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/phook.h"  # you most likely want to change this
  dnl if test -r $PHP_PHOOK/$SEARCH_FOR; then # path given as parameter
  dnl   PHOOK_DIR=$PHP_PHOOK
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for phook files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       PHOOK_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$PHOOK_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the phook distribution])
  dnl fi

  dnl Remove this code block if the library supports pkg-config.
  dnl --with-phook -> add include path
  dnl PHP_ADD_INCLUDE($PHOOK_DIR/include)

  dnl Remove this code block if the library supports pkg-config.
  dnl --with-phook -> check for lib and symbol presence
  dnl LIBNAME=PHOOK # you may want to change this
  dnl LIBSYMBOL=PHOOK # you most likely want to change this

  dnl If you need to check for a particular library function (e.g. a conditional
  dnl or version-dependent feature) and you are using pkg-config:
  dnl PHP_CHECK_LIBRARY($LIBNAME, $LIBSYMBOL,
  dnl [
  dnl   AC_DEFINE(HAVE_PHOOK_FEATURE, 1, [ ])
  dnl ],[
  dnl   AC_MSG_ERROR([FEATURE not supported by your phook library.])
  dnl ], [
  dnl   $LIBFOO_LIBS
  dnl ])

  dnl If you need to check for a particular library function (e.g. a conditional
  dnl or version-dependent feature) and you are not using pkg-config:
  dnl PHP_CHECK_LIBRARY($LIBNAME, $LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $PHOOK_DIR/$PHP_LIBDIR, PHOOK_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_PHOOK_FEATURE, 1, [ ])
  dnl ],[
  dnl   AC_MSG_ERROR([FEATURE not supported by your phook library.])
  dnl ],[
  dnl   -L$PHOOK_DIR/$PHP_LIBDIR -lm
  dnl ])
  dnl
  dnl PHP_SUBST(PHOOK_SHARED_LIBADD)

  dnl In case of no dependencies
  AC_DEFINE(HAVE_PHOOK, 1, [ Have phook support ])

  dnl Check PHP version and define compatibility macros
  AC_MSG_CHECKING([PHP version])
  PHP_VERSION=`$PHP_CONFIG --version`
  AC_MSG_RESULT([$PHP_VERSION])
  
  PHP_VERSION_MAJOR=`echo $PHP_VERSION | $AWK -F'.' '{print $1}'`
  PHP_VERSION_MINOR=`echo $PHP_VERSION | $AWK -F'.' '{print $2}'`
  PHP_VERSION_RELEASE=`echo $PHP_VERSION | $AWK -F'.' '{print $3}' | $AWK -F'-' '{print $1}'`
  
  PHP_VERSION_ID=`expr $PHP_VERSION_MAJOR \* 10000 + $PHP_VERSION_MINOR \* 100 + $PHP_VERSION_RELEASE`
  AC_DEFINE_UNQUOTED(PHP_VERSION_ID, $PHP_VERSION_ID, [PHP version ID])
  
  PHP_NEW_EXTENSION(phook, phook.c phook_observer.c, $ext_shared,, "-Wall -Wextra -Werror -Wno-unused-parameter")
fi

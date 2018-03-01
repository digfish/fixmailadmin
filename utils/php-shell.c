/**
execute a shell command provided in the args
after compiling set proper permissions:
$> chown root php_shell
$> chmod u=rwx,go=xr,+s php_root php_shell

NOTE: moving/copying the binary to another location will make the new instance to lose its needed permissions,
you'll have to explicitly provided them again as above

CREDITS: to Filip Roséen - refp as in https://stackoverflow.com/questions/8532304/execute-root-commands-via-php
*/

#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>
#include <string.h>

int main (int argc, char  **argv)
{
 setuid (0);

 char cmd[100] = "";
 int i;
 char *p;

 for(i=0; i < argc; i++) {
    if(i != 0){
   strcat(cmd, *(argv+i));
   strcat(cmd, " ");
    }
 }

 system (cmd);

 return 0;
}

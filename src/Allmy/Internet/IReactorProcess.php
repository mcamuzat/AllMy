<?php 
interface IReactorProcess {

    public function spawnProcess(processProtocol, executable, args=(), env={}, path=None,
                     uid=None, gid=None, usePTY=0, childFDs=None);
        /**
        Spawn a process, with a process protocol.

        @type processProtocol: L{IProcessProtocol} provider
        @param processProtocol: An object which will be notified of all
            events related to the created process.

        @param executable: the file name to spawn - the full path should be
                           used.

        @param args: the command line arguments to pass to the process; a
                     sequence of strings. The first string should be the
                     executable's name.

        @type env: a C{dict} mapping C{str} to C{str}, or C{None}.
        @param env: the environment variables to pass to the child process. The
                    resulting behavior varies between platforms. If
                      - C{env} is not set:
                        - On POSIX: pass an empty environment.
                        - On Windows: pass C{os.environ}.
                      - C{env} is C{None}:
                        - On POSIX: pass C{os.environ}.
                        - On Windows: pass C{os.environ}.
                      - C{env} is a C{dict}:
                        - On POSIX: pass the key/value pairs in C{env} as the
                          complete environment.
                        - On Windows: update C{os.environ} with the key/value
                          pairs in the C{dict} before passing it. As a
                          consequence of U{bug #1640
                          <http://twistedmatrix.com/trac/ticket/1640>}, passing
                          keys with empty values in an effort to unset
                          environment variables I{won't} unset them.

        @param path: the path to run the subprocess in - defaults to the
                     current directory.

        @param uid: user ID to run the subprocess as. (Only available on
                    POSIX systems.)

        @param gid: group ID to run the subprocess as. (Only available on
                    POSIX systems.)

        @param usePTY: if true, run this process in a pseudo-terminal.
                       optionally a tuple of C{(masterfd, slavefd, ttyname)},
                       in which case use those file descriptors.
                       (Not available on all systems.)

        @param childFDs: A dictionary mapping file descriptors in the new child
                         process to an integer or to the string 'r' or 'w'.

                         If the value is an integer, it specifies a file
                         descriptor in the parent process which will be mapped
                         to a file descriptor (specified by the key) in the
                         child process.  This is useful for things like inetd
                         and shell-like file redirection.

                         If it is the string 'r', a pipe will be created and
                         attached to the child at that file descriptor: the
                         child will be able to write to that file descriptor
                         and the parent will receive read notification via the
                         L{IProcessProtocol.childDataReceived} callback.  This
                         is useful for the child's stdout and stderr.

                         If it is the string 'w', similar setup to the previous
                         case will occur, with the pipe being readable by the
                         child instead of writeable.  The parent process can
                         write to that file descriptor using
                         L{IProcessTransport.writeToChild}.  This is useful for
                         the child's stdin.

                         If childFDs is not passed, the default behaviour is to
                         use a mapping that opens the usual stdin/stdout/stderr
                         pipes.

        @see: L{twisted.internet.protocol.ProcessProtocol}

        @return: An object which provides L{IProcessTransport}.

        @raise OSError: Raised with errno C{EAGAIN} or C{ENOMEM} if there are
                        insufficient system resources to create a new process.
        */
}

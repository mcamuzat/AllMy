<?php
/**
 *A basic abstraction for an error that has occurred.
 *
 *This is necessary because Python's built-in error mechanisms are
 *inconvenient for asynchronous communication.
 *
 *The C{stack} and C{frame} attributes contain frames.  Each frame is a tuple
 *of (funcName, fileName, lineNumber, localsItems, globalsItems), where
 *localsItems and globalsItems are the contents of
 *C{locals().items()}/C{globals().items()} for that frame, or an empty tuple
 *if those details were not captured.
 *
 *@ivar value: The exception instance responsible for this failure.
 *@ivar type: The exception's class.
 *@ivar stack: list of frames, innermost last, excluding C{Failure.__init__}.
 *@ivar frames: list of frames, innermost first.
 */
class Failure {

    private $pickled = 0
    private $stack = Null

    # The opcode of "yield" in Python bytecode. We need this in _findFailure in
    # order to identify whether an exception was thrown by a
    # throwExceptionIntoGenerator.
    _yieldOpcode = chr(opcode.opmap["YIELD_VALUE"])

        /**
        *Initialize me with an explanation of the error.
*
        *By default, this will use the current C{exception}
        *(L{sys.exc_info}()).  However, if you want to specify a
        *particular kind of failure, you can pass an exception as an
        *argument.

        *If no C{exc_value} is passed, then an "original" C{Failure} will
        *be searched for. If the current exception handler that this
        *C{Failure} is being constructed in is handling an exception
        *raised by L{raiseException}, then this C{Failure} will act like
        *the original C{Failure}.
*
        *For C{exc_tb} only L{traceback} instances or C{None} are allowed.
        *If C{None} is supplied for C{exc_value}, the value of C{exc_tb} is
        *ignored, otherwise if C{exc_tb} is C{None}, it will be found from
        *execution context (ie, L{sys.exc_info}).
*
        *@param captureVars: if set, capture locals and globals of stack
            *frames.  This is pretty slow, and makes no difference unless you
            *are going to use L{printDetailedTraceback}.
         */


    public function __construct($exc_value=Null, $exc_type=Null, $exc_tb=Null,
        captureVars=False)
    {
        global $count
        $count = $count + 1
        $this->count = count
        $this->type = $this->value = tb = None
        $this->captureVars = captureVars

        if isinstance(exc_value, str) and exc_type is None:
            raise TypeError("Strings are not supported by Failure")

        $stackOffset = 0

        if exc_value is None:
            exc_value = self._findFailure()

        if exc_value is None:
            self.type, self.value, tb = sys.exc_info()
            if self.type is None:
                raise NoCurrentExceptionError()
            stackOffset = 1
        elif exc_type is None:
            if isinstance(exc_value, Exception):
                self.type = exc_value.__class__
            else: #allow arbitrary objects.
                self.type = type(exc_value)
            self.value = exc_value
        else:
            self.type = exc_type
            self.value = exc_value
        if isinstance(self.value, Failure):
            self.__dict__ = self.value.__dict__
            return
        if tb is None:
            if exc_tb:
                tb = exc_tb
            elif _PY3:
                tb = self.value.__traceback__

        frames = self.frames = []
        stack = self.stack = []

        # added 2003-06-23 by Chris Armstrong. Yes, I actually have a
        # use case where I need this traceback object, and I've made
        # sure that it'll be cleaned up.
        self.tb = tb

        if tb:
            f = tb.tb_frame
        elif not isinstance(self.value, Failure):
            # we don't do frame introspection since it's expensive,
            # and if we were passed a plain exception with no
            # traceback, it's not useful anyway
            f = stackOffset = None

        while stackOffset and f:
            # This excludes this Failure.__init__ frame from the
            # stack, leaving it to start with our caller instead.
            f = f.f_back
            stackOffset -= 1

        # Keeps the *full* stack.  Formerly in spread.pb.print_excFullStack:
        #
        #   The need for this function arises from the fact that several
        #   PB classes have the peculiar habit of discarding exceptions
        #   with bareword "except:"s.  This premature exception
        #   catching means tracebacks generated here don't tend to show
        #   what called upon the PB object.

        while f:
            if captureVars:
                localz = f.f_locals.copy()
                if f.f_locals is f.f_globals:
                    globalz = {}
                else:
                    globalz = f.f_globals.copy()
                for d in globalz, localz:
                    if "__builtins__" in d:
                        del d["__builtins__"]
                localz = localz.items()
                globalz = globalz.items()
            else:
                localz = globalz = ()
            stack.insert(0, (
                f.f_code.co_name,
                f.f_code.co_filename,
                f.f_lineno,
                localz,
                globalz,
                ))
            f = f.f_back

        while tb is not None:
            f = tb.tb_frame
            if captureVars:
                localz = f.f_locals.copy()
                if f.f_locals is f.f_globals:
                    globalz = {}
                else:
                    globalz = f.f_globals.copy()
                for d in globalz, localz:
                    if "__builtins__" in d:
                        del d["__builtins__"]
                localz = list(localz.items())
                globalz = list(globalz.items())
            else:
                localz = globalz = ()
            frames.append((
                f.f_code.co_name,
                f.f_code.co_filename,
                tb.tb_lineno,
                localz,
                globalz,
                ))
            tb = tb.tb_next
        if inspect.isclass(self.type) and issubclass(self.type, Exception):
            parentCs = getmro(self.type)
            self.parents = list(map(reflect.qual, parentCs))
        else:
            self.parents = [self.type]
    }
        
/**Trap this failure if its type is in a predetermined list.
*
        *This allows you to trap a Failure in an error callback.  It will be
        *automatically re-raised if it is not a type that you expect.
*
        *The reason for having this particular API is because it's very useful
        *in Deferred errback chains::
*
            *def _ebFoo(self, failure):
                *r = failure.trap(Spam, Eggs)
                *print 'The Failure is due to either Spam or Eggs!'
                *if r == Spam:
                    *print 'Spam did it!'
                *elif r == Eggs:
                    *print 'Eggs did it!'
*
        *If the failure is not a Spam or an Eggs, then the Failure will be
        *'passed on' to the next errback. In Python 2 the Failure will be
        *raised; in Python 3 the underlying exception will be re-raised.
*
        *@type errorTypes: L{Exception}
        */

    public function trap(self, *errorTypes)
    {
        error = self.check(*errorTypes)
        if not error:
            if _PY3:
                self.raiseException()
            else:
                raise self
        return error
        /**Check if this failure's type is in a predetermined list.
         *
         *@type errorTypes: list of L{Exception} classes or
         *fully-qualified class names.
         *@returns: the matching L{Exception} type, or None if no match.
         */
    }
    public function check(self, *errorTypes)
    {
        for error in errorTypes:
            err = error
            if inspect.isclass(error) and issubclass(error, Exception):
                err = reflect.qual(error)
            if err in self.parents:
                return error
        return None
    }

    # It would be nice to use twisted.python.compat.reraise, but that breaks
    # the stack exploration in _findFailure; possibly this can be fixed in
    # #5931.
    if _PY3:
        def raiseException(self):
            raise self.value.with_traceback(self.tb)
    else:
        exec("""def raiseException(self):
    raise self.type, self.value, self.tb""")

    raiseException.__doc__ = (
        """
        raise the original exception, preserving traceback
        information if available.
        """)


    public function throwExceptionIntoGenerator(self, g):
        /**
        *Throw the original exception into the given generator,
        *preserving traceback information if available.
*
        *@return: The next value yielded from the generator.
        *@raise StopIteration: If there are no more values in the generator.
        *@raise anything else: Anything that the generator raises.
        */
        return g.throw(self.type, self.value, self.tb)


    public function _findFailure(cls):
        /**
        *Find the failure that represents the exception currently in context.
        */
        tb = sys.exc_info()[-1]
        if not tb:
            return

        secondLastTb = None
        lastTb = tb
        while lastTb.tb_next:
            secondLastTb = lastTb
            lastTb = lastTb.tb_next

        lastFrame = lastTb.tb_frame

        # NOTE: f_locals.get('self') is used rather than
        # f_locals['self'] because psyco frames do not contain
        # anything in their locals() dicts.  psyco makes debugging
        # difficult anyhow, so losing the Failure objects (and thus
        # the tracebacks) here when it is used is not that big a deal.

        # handle raiseException-originated exceptions
        if lastFrame.f_code is cls.raiseException.__code__:
            return lastFrame.f_locals.get('self')

        # handle throwExceptionIntoGenerator-originated exceptions
        # this is tricky, and differs if the exception was caught
        # inside the generator, or above it:

        # it is only really originating from
        # throwExceptionIntoGenerator if the bottom of the traceback
        # is a yield.
        # Pyrex and Cython extensions create traceback frames
        # with no co_code, but they can't yield so we know it's okay to just return here.
        if ((not lastFrame.f_code.co_code) or
            lastFrame.f_code.co_code[lastTb.tb_lasti] != cls._yieldOpcode):
            return

        # if the exception was caught above the generator.throw
        # (outside the generator), it will appear in the tb (as the
        # second last item):
        if secondLastTb:
            frame = secondLastTb.tb_frame
            if frame.f_code is cls.throwExceptionIntoGenerator.__code__:
                return frame.f_locals.get('self')

        # if the exception was caught below the generator.throw
        # (inside the generator), it will appear in the frames' linked
        # list, above the top-level traceback item (which must be the
        # generator frame itself, thus its caller is
        # throwExceptionIntoGenerator).
        frame = tb.tb_frame.f_back
        if frame and frame.f_code is cls.throwExceptionIntoGenerator.__code__:
            return frame.f_locals.get('self')

    _findFailure = classmethod(_findFailure)

    public function __repr__(self):
        return "<%s %s>" % (self.__class__, self.type)

    public function __str__(self):
        return "[Failure instance: %s]" % self.getBriefTraceback()

    public function __getstate__(self):
        /**Avoid pickling objects in the traceback.
        */
        if self.pickled:
            return self.__dict__
        c = self.__dict__.copy()

        c['frames'] = [
            [
                v[0], v[1], v[2],
                _safeReprVars(v[3]),
                _safeReprVars(v[4]),
            ] for v in self.frames
        ]

        # added 2003-06-23. See comment above in __init__
        c['tb'] = None

        if self.stack is not None:
            # XXX: This is a band-aid.  I can't figure out where these
            # (failure.stack is None) instances are coming from.
            c['stack'] = [
                [
                    v[0], v[1], v[2],
                    _safeReprVars(v[3]),
                    _safeReprVars(v[4]),
                ] for v in self.stack
            ]

        c['pickled'] = 1
        return c


        public function cleanFailure(self)
        {
        /**
        *Remove references to other objects, replacing them with strings.
*
        *On Python 3, this will also set the C{__traceback__} attribute of the
        *exception instance to C{None}.
        */
        self.__dict__ = self.__getstate__()
        if _PY3:
            self.value.__traceback__ = None
        }

    public function getTracebackObject(self)
    {
        /**
        *Get an object that represents this Failure's stack that can be passed
        *to traceback.extract_tb.

        *If the original traceback object is still present, return that. If this
        *traceback object has been lost but we still have the information,
        *return a fake traceback object (see L{_Traceback}). If there is no
        *traceback information at all, return None.
        **/
        if self.tb is not None:
            return self.tb
        elif len(self.frames) > 0:
            return _Traceback(self.frames)
        else:
            return None
    }
    public function getErrorMessage(self)
    {
        /**Get a string of the exception which caused this Failure.*/
        if isinstance(self.value, Failure):
            return self.value.getErrorMessage()
        return reflect.safe_str(self.value)
    }
    public function getBriefTraceback(self)
    {
        io = StringIO()
        self.printBriefTraceback(file=io)
        return io.getvalue()
    }
    public function getTraceback(self, elideFrameworkCode=0, detail='default')
    {
        io = StringIO()
        self.printTraceback(file=io, elideFrameworkCode=elideFrameworkCode, detail=detail)
        return io.getvalue()

    }
    public function printTraceback(self, file=None, elideFrameworkCode=False, detail='default')
    {
        /**
        Emulate Python's standard error reporting mechanism.

        @param file: If specified, a file-like object to which to write the
            traceback.

        @param elideFrameworkCode: A flag indicating whether to attempt to
            remove uninteresting frames from within Twisted itself from the
            output.

        @param detail: A string indicating how much information to include
            in the traceback.  Must be one of C{'brief'}, C{'default'}, or
            C{'verbose'}.
        */
        if file is None:
            from twisted.python import log
            file = log.logerr
        w = file.write

        if detail == 'verbose' and not self.captureVars:
            # We don't have any locals or globals, so rather than show them as
            # empty make the output explicitly say that we don't have them at
            # all.
            formatDetail = 'verbose-vars-not-captured'
        else:
            formatDetail = detail

        # Preamble
        if detail == 'verbose':
            w( '*--- Failure #%d%s---\n' %
               (self.count,
                (self.pickled and ' (pickled) ') or ' '))
        elif detail == 'brief':
            if self.frames:
                hasFrames = 'Traceback'
            else:
                hasFrames = 'Traceback (failure with no frames)'
            w("%s: %s: %s\n" % (
                    hasFrames,
                    reflect.safe_str(self.type),
                    reflect.safe_str(self.value)))
        else:
            w( 'Traceback (most recent call last):\n')

        # Frames, formatted in appropriate style
        if self.frames:
            if not elideFrameworkCode:
                format_frames(self.stack[-traceupLength:], w, formatDetail)
                w("%s\n" % (EXCEPTION_CAUGHT_HERE,))
            format_frames(self.frames, w, formatDetail)
        elif not detail == 'brief':
            # Yeah, it's not really a traceback, despite looking like one...
            w("Failure: ")

        # postamble, if any
        if not detail == 'brief':
            w("%s: %s\n" % (reflect.qual(self.type),
                            reflect.safe_str(self.value)))

        # chaining
        if isinstance(self.value, Failure):
            # TODO: indentation for chained failures?
            file.write(" (chained Failure)\n")
            self.value.printTraceback(file, elideFrameworkCode, detail)
        if detail == 'verbose':
            w('*--- End of Failure #%d ---\n' % self.count)

    }
    public function printBriefTraceback(self, file=None, elideFrameworkCode=0)
    {
        /**Print a traceback as densely as possible.
        */
        self.printTraceback(file, elideFrameworkCode, detail='brief')
    }
    public function printDetailedTraceback(self, file=None, elideFrameworkCode=0)
    {
        /**Print a traceback with detailed locals and globals information.
        */
        self.printTraceback(file, elideFrameworkCode, detail='verbose')
    }
}
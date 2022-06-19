# scopeRender
A basic library to provide simple and safe scope rendering

### About

The scoperenderer library will render partials in a manner such that the partial's
scope entirely rests withing a class of type Scope. Scope can be extended to set
your own properties on it, or you own methods. Partials are rendered using a 
ScopeRenderer, which has the methodology to handle preexisting buffered content.
Additionally the library contains a few basic escapers to sanitize output, though
you may extend these or create your own.

#### ScopeRenderer

This class is the basic class that will render partials. Note that partials are any
file, and are not restricted to php/phtml. You could render js, ts or some other 
output should you wish. When instantiated the ScopeRenderer needs a target directory
which the partials reside. When rendering output the ScopeRenderer will ensure that 
no partial is outside of that base directory, and will not allow path path manipulation
to get outside of that root directory. 

This sets up a basic renderer where the partials will all be stored in a directory below
the current one called 'partials'. The second parameter defines the extensions of the 
partials. By default 'phtml' is set, but I've included it here for clarity.

    $partialDir = __DIR__.DIRECTORY_SEPARATOR.'partials'
    $scopeRenderer = new ScopeRenderer($partialDir,'phtml');

Say you have a given scope, $scope, and a partial you wish to render, 'mypartial.phtml' you 
would get the rendered contents like so:

    $scopedOutput = $scopeRenderer->render('mypartial',$scope);

You would then output $scopedOutput however you wish. If you had a buffer going the 
buffer would be unchanged by your output, and your output is not added to the buffer!

The ScopeRenderer will not allow partials outside of the root to be renderered, so this
would fail:

    $scopedOutput = $scopeRenderer->render('..\mypartial',$scope);

#### Scope

The Scope class is what your partial will see as $this during rendering. You should 
make sure that your Scope class has all the methods or properties available the partial
will need. By default the Scope class will have a method escape() that provides the 
BasicEscaper class. You may rely on this, or extend the BasicEscaper and provide your
own escaper implementation. The basic escaper provides a javascript escaping method 
and a HTML escaping method. The HTML escaper is a whitelist escaper which means that 
when escaping values, anything that does not mean the passed parameters is removed or 
escaped. The arguments in the basic class are as such:

* $encodeAll - Attempt to encode everything, while respecting unlaying encoding. String is
is run through private method encodeAll
* $preserveFormatters - Relevant if $encodeAll is false. If this true, try and keep all 
the content matched by the patterns provided unchanged, and encode the rest. If false, remove
anything matched by the patterns and encode the rest. As default the constants on the 
BasicEscapeHtmlPatterns class are what is used, so for example, if $encodeAll is false, and
$preserveFormatters is true then '\<img src="foo.png"> an image' would have the img tag remain unescaped as it passes
the pattern in BASIC_PATTERN_IMAGES, but if $preserveFormatters is false, then it would be
removed from the string entirely, leaving only ' an image' behind.

The default escaper is injected into any scope provided to rendering unless the scope already
has an escaper set, such as via a call to setEscaper().

If you do not wish to use the libraries base escapers than you may extend ScopeAbstract 
into your own base scope class, update escape() on your scope class to have the correct return
type, and then either call setEscaper() on the scopeRenderer with your own escaper or inject
your escaper yourself into each scope before rendering. The escaper interface is an empty
interface, and there are no requirement on what escaper does, though in reality your escaper 
should have public methods that do some usefull work on a value provided.

#### Partials

For any scope you render, you need a partial, or a file, to output the content. The partial
can expect a single variable to be in scope, $this, where $this is the scope class provided. 
That means that the partial shoud also have access to the scope's properties, public and private
as well as the scope's methods, including the escaper. 

In more advanced usage you might have multiple base scopes, so that the escapers available to the
partials would be customized to provide methods only useful to partials of that type, such as a
html partial not having the same escaper as a javascript partial or a json partial or an xml partial.

By default the library does not provided a way to initiate a new partial render withing a partial,
aka sub partials, but that would be trivially easy to implement. To do so you could have a scope
that accepts as an argument either during constructor or via method call the scopeRenderer, and
then provide access to the render() method. Note that it would not be good practice to provide 
access directly to the scopeRenderer class itself, but instead have your scope implement it's own 
render method, so as to hide access to the other methods, like setRenderRootFolder().

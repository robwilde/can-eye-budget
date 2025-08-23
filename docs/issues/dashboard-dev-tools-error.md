When the page loads the dev tools shows this error.

```log
[vite] connecting... client:743:9
Uncaught TypeError: (intermediate value)(...) is not a function
    set checked http://127.0.0.1:8001/flux/flux.js?id=0cc768ef:128
    bindInputValue http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1997
    bind http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1971
    _x_forceModelUpdate http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:3406
    mutateDom http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:886
    _x_forceModelUpdate http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:3406
    <anonymous> http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:3413
    reactiveEffect http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:2432
    effect2 http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:2407
    effect http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:764
    wrappedEffect http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:780
    <anonymous> http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:3409
    flushHandlers http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1281
    stopDeferring http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1286
    deferHandlingDirectives http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1289
    initTree http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1479
    start http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1428
    start http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:1427
    start2 http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:8851
    <anonymous> http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:10215
    EventListener.handleEvent* http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:10211
    <anonymous> http://127.0.0.1:8001/livewire/livewire.js?id=df3a17f2:10218
flux.js:128:2528
[vite] connected. client:866:15
```

when I click the red "Direct Event Test" button.

```log
XHRPOST
http://127.0.0.1:8001/livewire/update
[HTTP/1.1 200 OK 70ms]

XHRGET
http://127.0.0.1:8001/_debugbar/open?op=get&id=01K3ASCNSBDQJ5MZ7JW15ZRWSN
[HTTP/1.1 200 OK 19ms]
```

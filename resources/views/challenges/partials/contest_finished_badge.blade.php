@if($contest->finished)
    <div class="bg-dark" style="padding: 16px; position: absolute; display:flex; align-items:center; justify-content:center; bottom: 0; left:0; right:0; text-align:center; background: rgba(0,0,0,0.55);">
        <span class="text-accent">{{\Illuminate\Support\Str::ucfirst($contestTitle)}} завершен</span>
    </div>
@endif

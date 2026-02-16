[select]<option value="{link}"[active] selected[/active]> {name} ({code})</option>[/select]
[link]<li><a href="{link}" class="text-uppercase d-block">{code}</a></li>[/link]
[button]
<button onclick="location.href='{link}'" data-language-option="{code}" class="w-full flex items-center space-x-3 px-4 py-3 text-left hover:bg-gold-600/10 transition-colors bg-gold-600/20 text-gold-400">
    <span class="text-lg">{code}</span>
    <span class="text-sm font-medium">{name}</span>
</button>
[/button]
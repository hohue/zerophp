        <p id="{{ $element['#id'] }}" class="form_item_content {{ $element['#class'] }}">
            @if (!empty($element['#prefix']))
                {{ $element['#prefix'] }}
            @endif
            
            {{ Form::{$element['#type']}($element['#value'], $element['#attributes']); }}

            @if (!empty($element['#description']))
                <span class="form_description">{{ $element['#description'] }}</span>
            @endif

            @if (!empty($element['#suffix']))
                {{ $element['#suffix'] }}
            @endif
        </p>
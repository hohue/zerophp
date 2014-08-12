    <div id="{{ $element['#id'] }}" class="{{ $element['#class'] }}">
        <p>
            @if (!empty($element['#title']))
                <label class="form_item_label">
                    {{ $element['#title'] }}
                    @if (!empty($element['#required']))
                        (<font>*</font>)
                    @endif
                </label>
            @endif

            <div class="form_item_content">
                @if (!empty($element['#prefix']))
                    {{ $element['#prefix'] }}
                @endif

                {{ Form::{$element['#type']}($element['#name'], $element['#attributes']); }}

                @if (!empty($element['#suffix']))
                    {{ $element['#suffix'] }}
                @endif

                @if (!empty($element['#description']))
                    <span class="form_description">{{ $element['#description'] }}</span>
                @endif

                @if (!empty($element['#error_messages']))
                    <font class="error-messages">{{ $element['#error_messages']?></font>
                @endif
            </div>
        </p>
    </div>
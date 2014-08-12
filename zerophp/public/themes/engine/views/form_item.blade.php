@if ($element['#type'] == 'hidden')
    {{ Form::hidden($element['#name'], $element['#value']); }}
@else
    @if ($type = zerophp_form_get_type()) @endif

    <div id="{{ $element['#id'] }}" class="{{ $element['#class'] }}">

        @if (!empty($element['#prefix']))
            {{ $element['#prefix'] }}
        @endif

        <p class="form_item_content">
            @if (in_array($element['#type'], $type[1]))
                {{ zerophp_form_content($element) }}
            @endif

            @if (!empty($element['#title']))
                {{-- Form::label($element['#id'], $element['#title']); --}}

                <label class="form_item_label">
                    {{ $element['#title'] }}
                    @if (!empty($element['#required']))
                        (<font>*</font>)
                    @endif
                </label>
            @endif

            @if (!in_array($element['#type'], $type[1]))
                {{ zerophp_form_content($element) }}
            @endif

            @if (!empty($element['#description']))
                <span class="form_description">{{ $element['#description'] }}</span>
            @endif

            @if (!empty($element['#error_messages']))
                <font class="error-messages">{{ $element['#error_messages']?></font>
            @endif
        </p>

        @if (!empty($element['#suffix']))
            {{ $element['#suffix'] }}
        @endif
    </div>
@endif

<div class="page">
  <ul>
    @for($i = 0; $i < COUNT($mag_sizes); $i++)
      <li id="drag_{{ rand(1, 999) }}" data-company="PLACEHOLDER" data-magazine="PLACEHOLDER" data-size="{{ $mag_sizes[$i]->package_size }}" draggable="true" ondragstart="drag(event)" >
        {{ $mag_sizes[$i]->package_name }}
      </li>
    @endfor
  </ul>
</div>

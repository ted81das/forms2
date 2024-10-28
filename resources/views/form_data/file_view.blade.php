@php
    $datas = implode(',', $form_upload);
    $files = explode(',', $datas);
@endphp
@foreach($files as $file)
    @php
        $url = Storage::url(config('constants.doc_path').'/'.$file);
        $path = public_path('uploads/'.config('constants.doc_path').'/'.$file);
    @endphp
    @if(file_exists($path))
        @php
            $media = explode('_', $file, 2);
            $file_name = !empty($media[1]) ? $media[1]: $media[0];
        @endphp
        @if(in_array(mime_content_type($path), ['image/jpeg', 'image/png', 'image/webp']))
            <a target="_blank" href="{{$url}}" download="{{$file_name}}">
                <img src="{{$url}}" alt="{{$file_name}}" style="width:100px;height: 90px;object-fit: contain;max-width: 100%;">
            </a>
        @else
            <a target="_blank" href="{{$url}}" download="{{$file_name}}">
                {{$file_name}}
            </a>
        @endif
        <br>
    @endif
@endforeach
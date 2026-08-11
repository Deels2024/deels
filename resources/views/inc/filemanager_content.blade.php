<div id="filemanager" class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-4">
                    <a href="{{route('load_filemanager')}}" data-toggle="tooltip" title="" id="button-refresh" class="btn btn-default" data-original-title="Refresh"><i class="fa fa-refresh"></i></a>

                    <button type="button" data-toggle="tooltip" title="Upload" id="button-upload" class="btn btn-primary"><i class="fa fa-upload"></i></button>
                    <button type="button" data-toggle="tooltip" title="Delete" id="button-delete" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>
                </div>

                <div class="col-sm-7">
                    <div class="input-group">
                        <input type="text" name="filemanager-search" value="{{request('filter_name')}}" placeholder="Search.." class="form-control">
                        <span class="input-group-btn">
            <button type="button" data-toggle="tooltip" title="Search" id="button-search" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </span></div>
                </div>

                <div class="col-sm-1">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-12">
                    <p class="mt-2 mb-0"> @lang('app.allowed_file_types') :
                        <code>{{implode(',',config('app.allowed_files'))}}</code>
                    </p>
                </div>
            </div>

            <hr />
            <div class="row">
                <div class="col-md-12">
                    <div id="statusMsg"></div>
                </div>
            </div>


            <div class="row">

                @if($medias->count())

                    @foreach($medias as $media)

                        <div class="col-sm-2 col-xs-3">
                            <div class="card">


                                @php
                                    $img_src = '';
                                    $media_uri = '';
                                    if($media->media_type() === 'image'){
                                        $img_src = media_image_uri($media->id)->thumbnail;
                                        $media_uri = media_image_uri($media->id)->thumbnail;
                                    }else{
                                        $img_src = $media->media_icon_url();
                                        $media_uri = media_file_uri($media->id);
                                    }
                                @endphp


                                <a href="{{$media_uri}}" class="thumbnail" {{media_image_uri_attr($media->id)}} >
                                    <img class="card-img-top" src="{{$img_src}}" alt="{{$media->name}}" title="{{$media->name}}" />
                                </a>


                                <div class="card-body">
                                    <label>
                                        <input type="checkbox" name="media_ids[]" value="{{$media->id}}" />
                                        {{$media->name}}
                                    </label>
                                </div>

                            </div>

                        </div>

                    @endforeach

                @else


                    <div class="col-6 offset-3">


                        <div class="media-upload-lg-wrap text-center">

                            <label id="button-upload2" class="upload-icon"> <i class="fa fa-cloud-upload"></i> </label>
                            <h5>Select files to upload</h5>
                        </div>

                    </div>

                @endif
            </div>


            <br />
        </div>

        @if($medias->lastPage() > 1)
            <div class="modal-footer">
                {!! $medias->appends(['filter_name' => request('filter_name')])->links() !!}
            </div>
        @endif
    </div>
</div>
@extends('layouts.admin.app')
@section('title') @if( ! empty($title)) {{ $title }} | @endif @parent @endsection


@section('content')
    <div class="dashboard-wrap">

        <div class="container">

            <div id="wrapper">

                @include('admin.menu')

                <div id="page-wrapper">
                    @if( ! empty($title))
                        <div class="row">
                            <div class="col-lg-12">
                                <h1 class="page-header"> {{ $title }}  </h1>
                            </div> <!-- /.col-lg-12 -->
                        </div> <!-- /.row -->
                    @endif

                    @include('admin.flash_msg')

                    <div class="row">
                        <div class="col-md-12">

                            <form action="" class="form-horizontal" method="post" enctype="multipart/form-data"> @csrf

                                <div class="form-group row {{ $errors->has('category_name')? 'has-error':'' }}">
                                    <label for="category_name" class="col-sm-4 control-label">@lang('app.category_name')</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="category_name" value="{{ old('category_name') }}" name="category_name" placeholder="@lang('app.category_name')">
                                        {!! $errors->has('category_name')? '<p class="help-block">'.$errors->first('category_name').'</p>':'' !!}
                                    </div>
                                </div>

                                <div class="form-group row {{ $errors->has('meta_title')? 'has-error':'' }}">
                                    <label for="meta_title" class="col-sm-4 control-label">Meta title</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="meta_title" value="{{ old('meta_title') }}" name="meta_title" placeholder="Meta title">
                                        {!! $errors->has('meta_title')? '<p class="help-block">'.$errors->first('meta_title').'</p>':'' !!}
                                    </div>
                                </div>

                                <div class="form-group row {{ $errors->has('meta_keywords')? 'has-error':'' }}">
                                    <label for="meta_keywords" class="col-sm-4 control-label">Meta keywords</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="meta_keywords" value="{{ old('meta_keywords') }}" name="meta_keywords" placeholder="Meta keywords">
                                        {!! $errors->has('meta_keywords')? '<p class="help-block">'.$errors->first('meta_keywords').'</p>':'' !!}
                                    </div>
                                </div>

                                <div class="form-group row {{ $errors->has('meta_description')? 'has-error':'' }}">
                                    <label for="meta_description" class="col-sm-4 control-label">Meta description</label>
                                    <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="meta_description" name="meta_description" placeholder="Meta description">

                                    </textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-offset-4 col-sm-8">
                                        <button type="submit" class="btn btn-primary">@lang('app.save_new_category')</button>
                                    </div>
                                </div>
                            </form>

                        </div>

                    </div>

                    @if($categories->count())
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered categories-lists">
                                    <tr>
                                        <th>@lang('app.category_name') </th>
                                        <th>@lang('app.action') </th>
                                    </tr>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td> {{ $category->category_name }}  </td>
                                            <td>
                                                <a href="{{ route('edit_categories', $category->id) }}" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="javascript:;" class="btn btn-danger btn-xs" data-id="{{ $category->id }}"><i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    @endif


                    <div class="clearfix"></div>
                </div>   <!-- /#page-wrapper -->


            </div>   <!-- /#wrapper -->


        </div> <!-- /#container -->

    </div>
@endsection

@section('page-js')
    <script>
		$(document).ready(function () {
			$('.btn-danger').on('click', function (e) {
				if (!confirm("Are you sure? its can't be undone")) {
					e.preventDefault();
					return false;
				}

				var selector = $(this);
				var data_id = $(this).data('id');

				$.ajax({
					type: 'POST',
					url: '{{ route('delete_categories') }}',
					data: {data_id: data_id, _token: '{{ csrf_token() }}'},
					success: function (data) {
						if (data.success == 1) {
							selector.closest('tr').hide('slow');
						}
					}
				});
			});
		});
    </script>
@endsection
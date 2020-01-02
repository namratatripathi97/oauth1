@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
        
    <div class="alert alert-success" style="display: none;" id="response"> 
    </div>     
  
</div>   
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Client') }}</div>     

                <div class="card-body"> 
                    <!-- <form method="POST" action="{{ route('addClient') }}">  --> 
                    <form method="POST" id="submitClient" action="#">           
                       <!--  @csrf -->
     
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <select id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                                    @foreach($integrationname as $int) 
                                    <option value="{{$int->name}}">{{$int->name}}</option>  
                                    @endforeach
                                </select>

                               <!--  <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus> -->

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror 
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="client_name" class="col-md-4 col-form-label text-md-right">{{ __('Client Name') }}</label> 

                            <div class="col-md-6">
                                <input id="client_name" type="text" class="form-control @error('client_name') is-invalid @enderror" name="client_name" value="{{ old('client_name') }}" required autocomplete="client_name" autofocus>

                                @error('client_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="url" class="col-md-4 col-form-label text-md-right">{{ __('Url') }}</label> 

                            <div class="col-md-6">
                                <input id="url" type="text" class="form-control @error('url') is-invalid @enderror" name="url" value="{{ old('url') }}"  autocomplete="url" autofocus>

                                @error('url')  
                                    <span class="invalid-feedback" role="alert"> 
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                         <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">{{ __('Api Call') }}</label> 

                            <div class="col-md-6">  
                                <input id="apicall" type="text" class="form-control @error('apicall') is-invalid @enderror" name="apicall" value="{{ old('apicall') }}"  autocomplete="apicall" autofocus>  
  
                                @error('apicall')   
                                    <span class="invalid-feedback" role="alert">  
                                        <strong>{{ $message }}</strong>  
                                    </span>  
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">{{ __('Username') }}</label> 

                            <div class="col-md-6">
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}"  autocomplete="username" autofocus>

                                @error('username')  
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                        

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"  autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror 
                            </div>
                        </div> 

                        <div class="form-group row">
                            <label for="client_id" class="col-md-4 col-form-label text-md-right">{{ __('Client ID') }}</label> 

                            <div class="col-md-6">
                                <input id="client_id" type="text" class="form-control @error('client_id') is-invalid @enderror" name="client_id" value="{{ old('client_id') }}"  autocomplete="client_id" autofocus>
 
                                @error('client_id')  
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="client_secret" class="col-md-4 col-form-label text-md-right">{{ __('Client Secret') }}</label> 

                            <div class="col-md-6">
                                <input id="client_secret" type="text" class="form-control @error('client_secret') is-invalid @enderror" name="client_secret" value="{{ old('client_secret') }}"  autocomplete="client_secret" autofocus>

                                @error('client_secret')   
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="access_token" class="col-md-4 col-form-label text-md-right">{{ __('Access Token') }}</label> 

                            <div class="col-md-6">
                                <input id="access_token" type="text" class="form-control @error('access_token') is-invalid @enderror" name="access_token" value="{{ old('access_token') }}"  autocomplete="access_token" autofocus>
  
                                @error('access_token')  
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="refresh_token" class="col-md-4 col-form-label text-md-right">{{ __('Refresh Token') }}</label> 

                            <div class="col-md-6">
                                <input id="refresh_token" type="text" class="form-control @error('refresh_token') is-invalid @enderror" name="refresh_token" value="{{ old('refresh_token') }}"  autocomplete="refresh_token" autofocus>

                                @error('refresh_token')  
                                    <span class="invalid-feedback" role="alert"> 
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">   
                            <label for="source" class="col-md-4 col-form-label text-md-right">{{ __('Source') }}</label> 

                            <div class="col-md-6"> 
                                <input id="source" type="text" class="form-control @error('source') is-invalid @enderror" name="source" value="{{ old('source') }}"  autocomplete="source" autofocus>   

                                @error('source')  
                                    <span class="invalid-feedback" role="alert">      
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror 
                            </div> 
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">  
                                <button type="submit" id="submit" class="btn btn-primary">
                                    {{ __('Add Client') }}     
                                </button>
                            </div>
                        </div>
                    </form>  
                </div>
            </div>
        </div>
    </div>
</div>
  
@endsection 
   
@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>  
    <script>
        $(document).ready(function()
        {
           /* alert('ss'); */
               $('#submitClient').submit(function() {  
                event.preventDefault();  
            var datastring = $(this).serialize();


                    $.ajax({
           type: "post",
           data: datastring,  
           url: "{{ route('addClient') }}",    
           success:function(data){
 
            /* */ 
                $("#response").show(); 
               $("#response").html(data);  
               //$("#msg").fadeOut(2000);
           }
       });
                 });  
  
        });
     
         
    </script>
@stop
    
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
                <div class="card-header">{{ __('Bullhorn Credential') }}</div>        

                <div class="card-body"> 
                    <!-- <form method="POST" action="{{ route('addClient') }}">  --> 
                    <form method="POST" id="submitClient" action="#">           
                       <!--  @csrf -->
     
                        
   
                    

                        <div class="form-group row">
                            <label for="client_id" class="col-md-4 col-form-label text-md-right">{{ __('Client ID') }}</label> 

                            <div class="col-md-6">
                                <input id="client_id" type="text" class="form-control @error('client_id') is-invalid @enderror" name="client_id" value="{{ old('client_id') }}"  required autocomplete="client_id" autofocus>
 
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
                                <input id="client_secret" type="text" class="form-control @error('client_secret') is-invalid @enderror" name="client_secret" value="{{ old('client_secret') }}"  required autocomplete="client_secret" autofocus>

                                @error('client_secret')   
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong> 
                                    </span>
                                @enderror
                            </div>
                        </div>
                            <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">{{ __('Username') }}</label> 

                            <div class="col-md-6">
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}"  autocomplete="username" required autofocus>

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
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"  autocomplete="new-password" required>
   
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror 
                            </div>
                        </div>    
                      
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">  
                                <button type="submit" id="submit" class="btn btn-primary">
                                    {{ __('Get Credential') }}     
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
               
          
               $('#submitClient').submit(function() { 
                event.preventDefault();  
            var datastring = $(this).serialize();


                    $.ajax({
           type: "post",   
           data: datastring,  
           url: "{{ route('editBullhorn') }}",    
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

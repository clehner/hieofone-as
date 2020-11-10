@extends('layouts.app')

@section('view.stylesheet')
	<style>
	html {
		position: relative;
		min-height: 100%;
	}
	body {
	/* Margin bottom by footer height */
		margin-bottom: 60px;
	}
	.footer {
		position: absolute;
		bottom: 0;
		width: 100%;
		/* Set the fixed height of the footer here */
		height: 60px;
		background-color: #f5f5f5;
	}
	.container .text-muted {
		margin: 20px 0;
	}
	.footer > .container {
		padding-right: 15px;
		padding-left: 15px;
	}
	</style>
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title" style="height:35px;display:table-cell !important;vertical-align:middle;">
						Login
						@if (isset($vp_request_url))
						with Credential Wallet
						@endif
	</h4>
				</div>
				<div class="panel-body">
					<div style="text-align: center;">
						<div style="text-align: center;">
							<i class="fa fa-child fa-5x" aria-hidden="true" style="margin:20px;text-align: center;"></i>
							@if ($errors->has('tryagain'))
								<div class="form-group has-error">
									<span class="help-block has-error">
										<strong>{{ $errors->first('tryagain') }}</strong>
									</span>
								</div>
							@endif
							@if (isset($message))
								<div class="form-group">
									<span class="help-block">
										<strong>{{ $message }}</strong>
									</span>
								</div>
							@endif
						</div>
					</div>
					@if (!isset($noform))
					<form class="form-horizontal" role="form" method="POST" action="{{ route('login_passwordless') }}">
						{{ csrf_field() }}
						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">Email </label>

							<div class="col-md-6">
								<input id="email" class="form-control" name="email" value="{{ old('email') }}" data-toggle="tooltip">

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-btn fa-envelope"></i> Send magic link
								</button>
							</div>
						</div>
						@if (!isset($nooauth))
						<div class="form-group">
							<div class="col-md-8 col-md-offset-2">
								<a href="{{ route('login_vp') }}" class="btn btn-primary btn-block">
									Login with Credential Wallet
								</a>
								<button type="button" class="btn btn-primary btn-block" id="addVerificationBtn"><i class="fa fa-btn fa-plus"></i> Add Doximity Clinician Verification</button>
								@if (isset($google))
									<a class="btn btn-primary btn-block" href="{{ url('/google') }}">
										<i class="fa fa-btn fa-google"></i> Login with Google
									</a>
								@endif
							</div>
						</div>
						@endif
					</form>
					@endif
					@if (isset($vp_request_url))
					<div class="form-group">
						<div style="text-align: center;">
							<p>Please scan this QR code with your credential wallet to proceed:</p>
							{!! QrCode::size(300)->generate($vp_request_url) !!}
							<p id="errors"></p>
					</div>
					@endif
					@if ($errors->has('tryagain') && isset($vp_received))
					<div class="col-md-6 col-md-offset-3">
						<a href="?retry_vp=1" class="btn btn-primary btn-block">
							Try again
						</a>
					</div>
					@endif
					@if (isset($get_email))
					<p>Please enter your email address to create your account.</p>
					<form class="form-horizontal" role="form" method="POST" action="">
						{{ csrf_field() }}
						<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
							<label for="email" class="col-md-4 control-label">Email </label>

							<div class="col-md-6">
								<input id="email" class="form-control" name="email" value="{{ old('email') }}" data-toggle="tooltip">

								@if ($errors->has('email'))
									<span class="help-block">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
								@endif
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									Continue
								</button>
							</div>
						</div>
					</form>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
<footer class="footer">
	<div class="container">
		<p class="text-muted pull-right">Version git-{{ $version }}</p>
	</div>
</footer>
<div class="modal" id="modal1" role="dialog">
	<div class="modal-dialog">
	  <!-- Modal content-->
		<div class="modal-content">
			<div id="modal1_header" class="modal-header">Add NPI credential to credential wallet?</div>
			<div id="modal1_body" class="modal-body" style="height:30vh;overflow-y:auto;">
				<p>This will simulate adding a verifiable credential to your existing credential wallet.</p>
				<p>Clicking on Get from Doximity will demonstrate how you can get a verifiable credential if you have an existing Doximity account.</p>
				<p>After the NPI credential is added, click on Login with Credential Wallet</p>
				<p>This will enable you to write a prescription.</p>
			</div>
			<div class="modal-footer">
				<a href="https://dir.hieofone.org/doximity_start/" target="_blank" class="btn btn-default" id="doximity_modal"><i class="fa fa-btn fa-hand-o-right"></i> Get from Doximity</a>
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-btn fa-times"></i> Close</button>
			  </div>
		</div>
	</div>
</div>
@endsection

@section('view.scripts')
<script type="text/javascript">
	$(document).ready(function() {
		$("#email").focus();
		$('[data-toggle="tooltip"]').tooltip();
		$("#addVerificationBtn").click(function(){
            $('#modal1').modal('show');
        });
		$('#doximity_modal').click(function(){
			$('#modal1').modal('hide');
		});
	});
	@if (isset($vp_request_url))
	var pollUrl = {!! json_encode(route('login_vp_poll')) !!};
	var csrfToken = $("meta[name='csrf-token']").attr('content');
	var interval = 3e3;
	function pollLogin() {
		var xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function () {
			if (this.readyState !== 4) return;
			switch (this.status) {
			case 200: // Login completed
				return location.reload();
			case 410: // VP request expired
				errors.innerText = 'Your QR code expired. Please reload the page to get a new one.';
				return;
			case 400: // VP request not in session
				errors.innerText = 'There was a problem coordinating with the server. Please reload the page to try again.';
				return;
			case 404: // VP request not in db
				errors.innerText = 'There was a problem with the request. Please reload the page to try again.';
				return;
			case 403: // Waiting
				errors.innerText = '';
				break;
			case 0: // Connection error
				errors.innerText = 'There was a problem communicating with the server. Are you offline?';
				break;
			}
			setTimeout(pollLogin, interval);
		};
		xhr.open('POST', pollUrl);
		xhr.setRequestHeader('X-CSRF-Token', csrfToken);
		xhr.send(null);
	}
	pollLogin()
	@endif
</script>
@endsection

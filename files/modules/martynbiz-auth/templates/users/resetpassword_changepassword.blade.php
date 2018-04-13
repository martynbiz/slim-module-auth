@extends('layouts.portal')

@section('content')
    <div class="col-sm-8 col-sm-offset-2 sso-register">
        <div class="row">
        	<div class="col-xs-12">
                <div class="well">
                    <form method="post" action="/accounts/resetpassword" id="passwordRecoveryEmailForm">
                        <p>{{ translate('resetpassword_change_password_intro') }}</p>

                        <div class="form-group">
                            <label for="password">{{ translate('password') }}</label>
                            <input type="password" class="form-control" name="password" placeholder="{{ translate('password') }}">
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">{{ translate('password_confirmation') }}</label>
                            <input type="password" class="form-control" name="password_confirmation" placeholder="{{ translate('password_confirmation') }}">
                        </div>

                        <?= $this->insert('martynbiz-core::partials/csrf_fields.phtml') ?>

                        <hr>

                        <div>
                            <button type="submit" class="btn btn-primary">{{ translate('change_password_button') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

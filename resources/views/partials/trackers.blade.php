{{-- Third-party analytics trackers — IDs configured in Admin → Analytics → Tracker Setup.
     Each block only fires if its Setting has been filled in, so this is a no-op until an
     admin actually pastes a key/ID. --}}
@php
    $__ga4Id      = \App\Models\Setting::get('ga4_measurement_id', '');
    $__posthogKey = \App\Models\Setting::get('posthog_key', '');
    $__posthogHost = \App\Models\Setting::get('posthog_host', 'https://us.i.posthog.com');
    $__clarityId  = \App\Models\Setting::get('clarity_project_id', '');
@endphp
@if($__ga4Id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $__ga4Id }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ $__ga4Id }}');
</script>
@endif
@if($__posthogKey)
<script>
  !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSurveysLoaded onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
  posthog.init('{{ $__posthogKey }}', { api_host: '{{ $__posthogHost }}', person_profiles: 'identified_only' });
  @auth
  posthog.identify('{{ auth()->id() }}', { level: {{ (int) (auth()->user()->progress->level ?? 1) }} });
  @endauth
</script>
@endif
@if($__clarityId)
<script>
  (function(c,l,a,r,i,t,y){
      c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
      t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
      y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
  })(window, document, "clarity", "script", "{{ $__clarityId }}");
</script>
@endif

package nl.jelmervanderlinde.ChildrensGame;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.ConsoleMessage;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Toast;

public class GameActivity extends Activity {

    private static final String LOG_TAG = "GameActivity";

    @Override
    protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);

        // Disable the application title bar
        requestWindowFeature(Window.FEATURE_NO_TITLE);

        setContentView(R.layout.activity_fullscreen);

        // Capture long click events which toggle the controls
        View contentView = findViewById(R.id.webView);
        contentView.setOnLongClickListener(new View.OnLongClickListener() {
            @Override
            public boolean onLongClick(View view) {
                toggleControls();
                return true;
            }
        });
        // Initially hide the controls
        toggleControls();

        // Init the webview
        initWebView();
    }

    private void initWebView()
    {
        WebView browser = (WebView) findViewById(R.id.webView);
        browser.getSettings().setJavaScriptEnabled(true);
        browser.addJavascriptInterface(new WebAudioAPI(this, browser), "globalAudio");

        String databasePath = this.getApplicationContext().getDir("database", Context.MODE_PRIVATE).getPath();
        browser.getSettings().setDatabasePath(databasePath);
        browser.getSettings().setDatabaseEnabled(true);
        browser.getSettings().setDomStorageEnabled(true);

        String cachePath = this.getApplicationContext().getDir("cache", Context.MODE_PRIVATE).getPath();
        browser.getSettings().setAppCachePath(cachePath);
        browser.getSettings().setAppCacheEnabled(true);

        browser.getSettings().setSupportZoom(false);
        browser.getSettings().setMediaPlaybackRequiresUserGesture(false);

        final Activity activity = this;
        browser.setWebChromeClient(new WebChromeClient() {
            @Override
            public boolean onJsAlert(WebView view, String url, String message, JsResult result) {
                Log.d(LOG_TAG, message);

                new AlertDialog.Builder(view.getContext()).setMessage(message).setCancelable(true).show();
                result.confirm();

                return true;
            }

            @Override
            public boolean onConsoleMessage(ConsoleMessage message) {
                Log.d(LOG_TAG, String.format("%s @ %d: %s",
                        message.message(), message.lineNumber(), message.sourceId()));

                return true;
            }
        });
//
        browser.setWebViewClient(new WebViewClient() {
            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingURL) {
                Log.d(LOG_TAG, description);
                Toast.makeText(activity, description, Toast.LENGTH_SHORT).show();
            }
        });

        browser.loadUrl("file:///android_asset/www/index.html");
    }

    private void toggleControls()
    {
        View controlsView = findViewById(R.id.fullscreen_content_controls);
        boolean visible = controlsView.getVisibility() == View.VISIBLE;
        controlsView.setVisibility(visible ? View.VISIBLE : View.GONE);
        boolean visible = controlsView.getVisibility() != View.GONE;
        controlsView.setVisibility(visible ? View.GONE : View.VISIBLE);
    }
}

package nl.jelmervanderlinde.ChildrensGame;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.view.Window;
import android.webkit.ConsoleMessage;
import android.webkit.JavascriptInterface;
import android.webkit.JsResult;
import android.webkit.WebChromeClient;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.TextView;

import org.apache.http.HttpResponse;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.InputStreamEntity;
import org.apache.http.impl.client.DefaultHttpClient;

import java.io.File;
import java.io.FileFilter;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.UUID;

public class GameActivity extends Activity {

    private static final String LOG_TAG = "GameActivity";

    private WebAudioAPI audioAPI;

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

        // Catch clicks on the restart button which reload the web view
        Button restartButton = (Button) findViewById(R.id.restart_button);
        restartButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                restart();
            }
        });

        // Catch long presses on the status text to enable clearing the queue
        final Activity activity = this;
        View statusText = findViewById(R.id.queue_status);
        statusText.setOnLongClickListener(new View.OnLongClickListener() {
            @Override
            public boolean onLongClick(View view) {
                new AlertDialog.Builder(activity)
                        .setTitle("Delete measurements")
                        .setMessage("Do you really want to delete unsubmitted measurements?")
                        .setPositiveButton("Delete", new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialogInterface, int i) {
                                clearSubmissionQueue();
                            }
                        })
                        .setNegativeButton("No", null)
                        .show();

                return true;
            }
        });

        // Initially hide the controls
        toggleControls();

        // Update the queue status text
        updateQueueStatus();

        // Sync any submissions still in the queue
        syncMeasurementSubmissionQueue();

        // Init the web view
        initWebView();

        // Start the game
        restart();
    }

    @Override
    protected void onPause()
    {
        super.onPause();
        audioAPI.pause();
    }

    @Override
    protected void onResume()
    {
        super.onResume();
        audioAPI.resume();
    }

    private void initWebView()
    {
        WebView browser = (WebView) findViewById(R.id.webView);
        browser.getSettings().setJavaScriptEnabled(true);

        audioAPI = new WebAudioAPI(this, browser);
        browser.addJavascriptInterface(audioAPI, "globalAudio");
        browser.addJavascriptInterface(this, "activity");

        String databasePath = this.getApplicationContext().getDir("database", Context.MODE_PRIVATE).getPath();
        browser.getSettings().setDatabasePath(databasePath);
        browser.getSettings().setDatabaseEnabled(true);
        browser.getSettings().setDomStorageEnabled(true);

        String cachePath = this.getApplicationContext().getDir("cache", Context.MODE_PRIVATE).getPath();
        browser.getSettings().setAppCachePath(cachePath);
        browser.getSettings().setAppCacheEnabled(true);

        browser.getSettings().setSupportZoom(false);
//        browser.getSettings().setMediaPlaybackRequiresUserGesture(false);

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

        browser.setWebViewClient(new WebViewClient() {
            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingURL) {
                Log.d(LOG_TAG, description);
            }
        });
    }

    private void restart()
    {
        // Make sure the audio is stopped
        audioAPI.stop();

        // Reload the web page in the web view
        WebView browser = (WebView) findViewById(R.id.webView);
        browser.loadUrl("file:///android_asset/www/index.html");
    }

    private void toggleControls()
    {
        View controlsView = findViewById(R.id.fullscreen_content_controls);
        boolean visible = controlsView.getVisibility() != View.GONE;
        controlsView.setVisibility(visible ? View.GONE : View.VISIBLE);
    }

    private void updateQueueStatus()
    {
        TextView status = (TextView) findViewById(R.id.queue_status);
        File[] queue = getMeasurementSubmissionQueue();
        int queueLength = queue == null ? 0 : queue.length;

        status.setVisibility(queueLength > 0 ? View.VISIBLE : View.GONE);
        status.setText(String.format("%d pending submissions", queueLength));
    }

    @JavascriptInterface
    public void submitMeasurements(String json)
    {
        Log.d(LOG_TAG, "Receiving measurements " + json);

        String unique_id = UUID.randomUUID().toString();
        String filename = unique_id + ".txt";

        File root = getFileStreamPath("measurements_queue");
        if (!root.exists() || !root.isDirectory()) {
            Log.d(LOG_TAG, root + " does not exist, creating directory");
            root.mkdir();
        }

        Log.d(LOG_TAG, "Writing measurements to " + filename);
        try {
            FileOutputStream queue = new FileOutputStream(new File(root.getAbsolutePath() + "/" + filename));
            queue.write(json.getBytes());
            queue.close();
        } catch (IOException e) {
            e.printStackTrace();
        }

        Log.d(LOG_TAG, "Finished writing");

        syncMeasurementSubmissionQueue();
    }

    public File[] getMeasurementSubmissionQueue()
    {
        File root = getFileStreamPath("measurements_queue");

        return root.listFiles(new FileFilter() {
            @Override
            public boolean accept(File file) {
                return file.getName().endsWith(".txt");
            }
        });
    }

    public void syncMeasurementSubmissionQueue()
    {
        Log.d(LOG_TAG, "Syncing measurements");

        File[] measurements = getMeasurementSubmissionQueue();
        if (measurements == null)
            return;

        Log.d(LOG_TAG, "Submitting " + measurements.length + " measurements to server");

        AsyncTask<File, Void, Boolean> task = new AsyncTask<File, Void, Boolean>() {
            @Override
            protected Boolean doInBackground(File... files) {
                for (File file : files)
                    if (submitMeasurementToAPI(file))
                        file.delete();

                return true;
            }

            protected void onPostExecute(Boolean result) {
                runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        updateQueueStatus();
                    }
                });
            }
        };

        task.execute(measurements);
    }

    public void clearSubmissionQueue()
    {
        for (File file : getMeasurementSubmissionQueue())
            file.delete();

        updateQueueStatus();
    }

    private boolean submitMeasurementToAPI(File measurement)
    {
        HttpClient client = new DefaultHttpClient();
        HttpPost postRequest = new HttpPost("http://www.philos.rug.nl/cgm/franziska/post-measurements.php");
        FileInputStream in = null;

        try {
            in = new FileInputStream(measurement);
            postRequest.setEntity(new InputStreamEntity(in, -1));

            HttpResponse response = client.execute(postRequest);

            if (response.getStatusLine().getStatusCode() == 200)
                return true;

            Log.d(LOG_TAG, "Response: " + response.getStatusLine().toString());
            Log.d(LOG_TAG, response.getEntity().toString());
            return false;
        } catch (FileNotFoundException e) {
            e.printStackTrace();
            return false;
        } catch (ClientProtocolException e) {
            e.printStackTrace();
            return false;
        } catch (IOException e) {
            e.printStackTrace();
            return false;
        } finally {
            if (in != null) {
                try {
                    in.close();
                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        }
    }
}

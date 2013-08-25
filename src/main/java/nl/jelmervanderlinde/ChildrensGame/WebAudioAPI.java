package nl.jelmervanderlinde.ChildrensGame;

import android.app.Activity;
import android.content.res.AssetFileDescriptor;
import android.media.AudioManager;
import android.media.MediaPlayer;
import android.util.Log;
import android.webkit.JavascriptInterface;
import android.webkit.WebView;

import java.io.IOException;

/**
 * Created by Jelmer van der Linde on 8/24/13.
 */
public class WebAudioAPI
{
    private WebView browser;

    private Activity parent;

    private MediaPlayer player;

    public WebAudioAPI(Activity parent, WebView browser)
    {
        this.parent = parent;
        this.browser = browser;
    }

    protected void callback(final String script)
    {
        parent.runOnUiThread(new Runnable() {
            @Override
            public void run() {
                browser.loadUrl("javascript:" + script);
            }
        });
    }

    @JavascriptInterface
    public void play(String assetURI, final String startCallback, final String finishCallback, final String errorCallback)
    {
        if (player != null)
            release();

//        Log.d("ChildrensGame.WebAudioAPI", assetURI);

        try {
            player = new MediaPlayer();
            player.setAudioStreamType(AudioManager.STREAM_MUSIC);

            AssetFileDescriptor afd = parent.getApplicationContext().getAssets().openFd(assetURI);
            player.setDataSource(afd.getFileDescriptor(), afd.getStartOffset(), afd.getLength());
            afd.close();

            player.setOnCompletionListener(new MediaPlayer.OnCompletionListener() {
                @Override
                public void onCompletion(MediaPlayer mediaPlayer) {
                    release();
                    callback(finishCallback);
                }
            });

            player.prepare();
            player.start();

            callback(startCallback);
        } catch (IOException e) {
            e.printStackTrace();
            release();
            callback(errorCallback);
        }
    }

    @JavascriptInterface
    public void stop()
    {
        if (player == null)
            return;

        player.stop();
        release();
    }

    @JavascriptInterface
    public boolean isPlaying()
    {
        return player != null && player.isPlaying();
    }

    public void pause()
    {
        if (player != null)
            player.pause();
    }

    public void resume()
    {
        if (player != null)
            player.start();
    }

    private void release()
    {
        if (player == null)
            return;

        player.stop();
        player.release();
        player = null;
    }

    @JavascriptInterface
    public String toString()
    {
        return "WebAudioAPI";
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\PestsAndDisease;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\UploadedFile;

use App\Models\Crop;
use App\Models\GroundnutVariety;
//storage
use Illuminate\Support\Facades\Storage;
//getClientOriginalExtension


class PestsAndDiseaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PestsAndDisease';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PestsAndDisease());

        //show a user only their gardens
        $user = auth()->user();
        if(!$user->isRole('administrator')){
          $grid->model()->where('user_id', $user->id);
        }
        //filter by garden name
        $grid->filter(function($filter) use($user){
            //disable the default id filter
            $filter->disableIdFilter();
            $filter->like('category', 'Category');
        });

        //disable  column selector
        $grid->disableColumnSelector();

        //disable export
        $grid->disableExport();

        $grid->column('garden_location', __('Garden location'));
        $grid->column('user_id', __('User'))->display(function($user_id) {
            return \App\Models\User::find($user_id)->name;
        });
        // $grid->column('variety_id', __('Variety'))->display(function($variety_id) {
        //     return \App\Models\GroundnutVariety::find($variety_id)->name;
        // });
        $grid->column('category', __('Category'));
  
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(PestsAndDisease::findOrFail($id));

        $show->field('garden_location', __('Garden location'));
        $show->field('user_id', __('User id'))->as(function($user_id){
            return \App\Models\User::find($user_id)->name;
        });
        // $show->field('variety_id', __('Variety'))->as(function($variety_id){
        //     return \App\Models\GroundnutVariety::find($variety_id)->name;
        // });
        $show->field('category', __('Category'));
        $show->field('photo', __('Photo'))->image();
        $show->field('video', __('Video'))->video();
        $show->field('audio', __('Audio'))->audio();
        $show->field('description', __('Description'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PestsAndDisease());

        $user = auth()->user();

        //When form is creating, assign user id
        if ($form->isCreating()) 
        {
            $form->hidden('user_id')->default($user->id);

        }

         //onsaved return to the list page
         $form->saved(function (Form $form) 
        {
            admin_toastr(__('Query submitted successfully'), 'success');
            return redirect('/pests-and-diseases');
        });

     
// $form->saving(function (Form $form) {
//     // Use request() to get the submitted value
//     $audioInput = request('audioInput');
//     error_log('audioInput: ' . $audioInput);

//     if ($audioInput) {
//         $audioExtension = pathinfo($audioInput, PATHINFO_EXTENSION);
//         $audioName = 'audio_' . time() . '.' . $audioExtension;
//         $audioBinary = base64_decode(str_replace('data:audio/' . $audioExtension . ';base64,', '', $audioInput));

//         // Save the file to storage using UploadedFile
//         $tempFilePath = tempnam(sys_get_temp_dir(), 'audio_temp_');
//         file_put_contents($tempFilePath, $audioBinary);
        
//         $uploadedFile = new UploadedFile(
//             $tempFilePath,
//             $audioName,
//             'audio/' . $audioExtension,
//             null,
//             true // Set to true to enable "test" mode
//         );

//         Storage::disk('public')->put($audioName, $uploadedFile->getContent());

//         // Update the form field value
//         $form->audio = $audioName;

//         // Delete the temporary file
//         unlink($tempFilePath);
//     }
// });
        
       

       

      
    //     $form->html('<button type="button" id="startRecordingButton">Start Recording</button>');
    //     $form->html('<button type="button" id="stopRecordingButton" style="display: none;">Stop Recording</button>');
    //     $form->html('<audio id="audioPlayer" controls></audio>');
    //     $form->html('<input type="hidden" id="audioInput" name="audioInput">');
         $form->file('audio', __('Audio Input'));
    //     $form->html('<script>
    //     var startRecordingButton = document.getElementById("startRecordingButton");
    //     var stopRecordingButton = document.getElementById("stopRecordingButton");
    //     var audioPlayer = document.getElementById("audioPlayer");
    //     var audioInput = document.getElementById("audioInput");
    //     var mediaRecorder;
    //     var audioChunks = [];
        
    //     startRecordingButton.addEventListener("click", startRecording);
    //     stopRecordingButton.addEventListener("click", stopRecording);
        
    //     function startRecording() {
    //         navigator.mediaDevices.getUserMedia({ audio: true })
    //             .then(function (stream) {
    //                 mediaRecorder = new MediaRecorder(stream);
        
    //                 mediaRecorder.ondataavailable = function (event) {
    //                     if (event.data.size > 0) {
    //                         audioChunks.push(event.data);
    //                     }
    //                 };
        
    //                 mediaRecorder.onstop = function () {
    //                     var audioBlob = new Blob(audioChunks, { type: "audio/wav" });
        
    //                     // Convert the Blob to an audio file
    //                     const audioUrl = URL.createObjectURL(audioBlob);
        
    //                     // Use the audioUrl for playback or other purposes if needed
    //                     audioPlayer.src = audioUrl;
        
    //                     // Create FormData and append the audioBlob
    //                     var formData = new FormData();
    //                     formData.append("audioInput", audioBlob, "audio.wav");
        
    //                     // Update the value of audioInput
    //                     audioInput.value = "audio.wav";
    //                 };
        
    //                 mediaRecorder.start();
    //                 startRecordingButton.style.display = "none";
    //                 stopRecordingButton.style.display = "block";
    //             })
    //             .catch(function (error) {
    //                 console.error("Error accessing microphone:", error);
    //             });
    //     }
        
    //     function stopRecording() {
    //         mediaRecorder.stop();
    //         startRecordingButton.style.display = "block";
    //         stopRecordingButton.style.display = "none";
    //     }
        
    // </script>');
    

     
        //add a get gps coordinate button

        $form->html('<button type="button" id="getLocationButton" style="background-color: darkgreen; color: white;">' . __('Get GPS Coordinates') . '</button>');


        $form->text('garden_location', __('Garden location'))->attribute([
            'id' => 'coordinates',   
        ])->required();
     
        
        //script to get the gps coordinates
        Admin::script(<<<SCRIPT
            document.getElementById('getLocationButton').addEventListener('click', function() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById('coordinates').value = position.coords.latitude + ', ' + position.coords.longitude;
                    });
                } else {
                    alert('Geolocation is not supported by your browser.');
                }
            });
        SCRIPT);
        //$form->select('variety_id', __('Select crop variety '))->options(GroundnutVariety::pluck('name', 'id'))->rules('required');
        $form->text('category', __('Select Inquiries category'))->options([
            'Extension'=>'Extension',
            'Query'=>'Query',
        ]);
        $form->file('photo', __('Photo'));
        $form->file('video', __('Video'));
        //$form->hidden('audio', __('Audio'));

        $form->text('description', __('Description'));

        return $form;
    }
}

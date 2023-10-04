
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Answers</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file here -->
    <style>
        /* Add CSS for separating answers with lines */
        .answer_list {
            border-bottom: 1px solid #ccc; /* Add a bottom border to create lines between answers */
            padding-bottom: 20px; /* Adjust the spacing as needed */
            margin-bottom: 20px; /* Adjust the spacing as needed */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h1>{{$question}}</h1>
            </div>
            <div class="card-body">
                <!-- Loop through the answers and display them here -->
                @foreach($answers as $answer)
                @php
                 $user = App\Models\User::find($answer->user_id)->name;
                 $date = $answer->created_at->format('Y-m-d'); // Format it as 'YYYY-MM-DD HH:MM:SS'

                @endphp
                <div class="answer_list">
                    <h4>{{ $answer->answer }}</h4>
                    <p class="asked-by">Answered by: {{ $user }}</p>
                    <p class="question-date">Date: {{ $date }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>



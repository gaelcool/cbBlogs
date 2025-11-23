<!DOCTYPE html>
<html>
    <body>
 <div>  bigger container
    <div>  blog section
    </div>

    <div> Comment section
        <div> CommentContainer  //holds comment header with commenter info and the commentscontent 
            <div>Comment header
                <a> conmment header holds the Username of comementer and their grade</a></div> 
            <div> CommentContent  //holds comment content so should be a child of commentContainer
                <a>preview of blog which pulls the CONTENTT variable from the post table and displays a trimmed version of it</a></div> 
    </div>
</div>
    

 >lines 91 onward:
   <h2 class="comments-header">
                <?php if ($selectedPostId): ?>
                    Comments (<?php echo count($comments); ?>)
                <?php else: ?>
                    Select a blog to view comments
                <?php endif; ?>
            </h2>
            
            <?php if ($selectedPostId && !empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>

                    <div class = "CommentSection">
    <div class ="CommentContainer">
     <div class = "CommentHeader">
        <h2>"Username of commenter"</h2> <p>"Commenters grade"</p>
            </div>
    <div class = "CommentContent" >
        <a>"Return trimmed comment"</a> 
    
    </div>

        </div>
    </div>
</div>
                </body>
</html> 
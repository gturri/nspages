package nspages;

import static org.junit.Assert.assertTrue;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_noRegIssue153 extends Helper {
    @Test
    public void testNoReg(){
        generatePage("noRegPR123:start", "<nspages\n-exclude:page\n>");

        // Assert there is at least one link. ie: assert that it does not display "this namespace doesn't exist: 0"
        assertTrue(getNspagesLinks().size() > 0);
    }
}
